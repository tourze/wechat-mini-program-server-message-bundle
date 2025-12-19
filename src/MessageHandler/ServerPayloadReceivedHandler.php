<?php

namespace WechatMiniProgramServerMessageBundle\MessageHandler;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Service\UserTransformService;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Json\Json;

#[AsMessageHandler]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'wechat_mini_program_server_message')]
final class ServerPayloadReceivedHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly AccountRepository $accountRepository,
        #[Autowire(service: 'cache.app')] private readonly AdapterInterface $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserTransformService $userTransformService,
        private readonly ?UserLoaderInterface $userLoader = null,
    ) {
    }

    public function __invoke(ServerPayloadReceivedMessage $message): void
    {
        $payload = $message->getPayload();
        if ([] === $payload) {
            throw new UnrecoverableMessageHandlingException('回调数据Payload不能为空');
        }

        $account = $this->getAccount($message->getAccountId());

        if (!$this->isValidPayload($payload)) {
            return;
        }

        $msgId = $this->generateMsgId($payload);

        if ($this->isDuplicateMessage($msgId)) {
            return;
        }

        $shouldContinue = $this->saveServerMessage($payload, $account, $msgId);
        if (!$shouldContinue) {
            return;
        }

        $localUser = $this->processUser($payload, $account);
        $this->dispatchEvent($payload, $account, $localUser);
        $this->markMessageProcessed($msgId);
    }

    private function getAccount(string $accountId): Account
    {
        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            throw new UnrecoverableMessageHandlingException('找不到小程序账号');
        }

        return $account;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isValidPayload(array $payload): bool
    {
        return isset($payload['CreateTime']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function generateMsgId(array $payload): string
    {
        $msgId = ArrayHelper::getValue($payload, 'MsgId', '');
        if ('' === $msgId) {
            $msgId = 'GEN-' . md5(Json::encode($payload));
        }

        return strval($msgId);
    }

    private function isDuplicateMessage(string $msgId): bool
    {
        $cacheKey = md5(ServerMessageRequestEvent::class . $msgId);
        if ($this->cache->hasItem($cacheKey)) {
            $this->logger->warning('缓存存在了，即为处理过了', ['cacheKey' => $cacheKey]);

            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function saveServerMessage(array $payload, Account $account, string $msgId): bool
    {
        $entity = new ServerMessage();
        $entity->setAccount($account);
        $entity->setMsgId($msgId);
        $entity->setMsgType($payload['MsgType']);
        $entity->setToUserName($payload['ToUserName']);
        $entity->setFromUserName($payload['FromUserName']);
        $entity->setCreateTime(CarbonImmutable::createFromTimestamp($payload['CreateTime'], date_default_timezone_get()));
        $entity->setRawData($payload);

        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return true;
        } catch (UniqueConstraintViolationException $exception) {
            $this->logger->warning('服务端通知重复，不处理', [
                'message' => $payload,
                'exception' => $exception,
            ]);

            return false;
        } catch (ORMInvalidArgumentException $exception) {
            $this->logger->error('保存失败未知原因', [
                'message' => $payload,
                'exception' => $exception,
            ]);

            return true;
        } catch (\Throwable $exception) {
            $this->logger->error('记录服务端消息时发生错误', [
                'exception' => $exception,
                'message' => $entity,
            ]);

            return true;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processUser(array $payload, Account $account): UserInterface
    {
        if (null === $this->userLoader) {
            $this->logger->warning('UserLoaderInterface not available, creating new user');

            return $this->createNewUser($payload, $account);
        }

        $localUser = $this->userLoader->loadUserByOpenId($payload['FromUserName']);
        if (null === $localUser) {
            $localUser = $this->createNewUser($payload, $account);
        }

        if ($localUser instanceof User) {
            $this->userTransformService->transformToSysUser($localUser);
        }

        return $localUser;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createNewUser(array $payload, Account $account): User
    {
        $localUser = new User();
        $localUser->setAccount($account);
        $localUser->setOpenId($payload['FromUserName']);
        $localUser->setLanguage(Language::zh_CN);
        $localUser->setRawData(Json::encode($payload));
        $this->entityManager->persist($localUser);
        $this->entityManager->flush();

        return $localUser;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function dispatchEvent(array $payload, Account $account, UserInterface $localUser): void
    {
        $event = new ServerMessageRequestEvent();
        $event->setMessage($payload);
        $event->setAccount($account);
        $event->setWechatUser($localUser);

        try {
            $this->eventDispatcher->dispatch($event);
        } catch (\Throwable $exception) {
            $this->logger->error('分发微信小程序服务端回调消息时发生错误', [
                'exception' => $exception,
                'event' => $event,
            ]);
        }
    }

    private function markMessageProcessed(string $msgId): void
    {
        $cacheKey = md5(ServerMessageRequestEvent::class . $msgId);
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set(time());
        $cacheItem->expiresAfter(60 * 60);
        $this->cache->save($cacheItem);
    }
}
