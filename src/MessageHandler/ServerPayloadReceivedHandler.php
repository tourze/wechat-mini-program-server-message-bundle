<?php

namespace WechatMiniProgramServerMessageBundle\MessageHandler;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Json\Json;

#[AsMessageHandler]
class ServerPayloadReceivedHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserLoaderInterface $userLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly AccountRepository $accountRepository,
        private readonly CacheInterface $cache,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ServerPayloadReceivedMessage $message): void
    {
        $payload = $message->getPayload();
        if ((bool) empty($payload)) {
            throw new UnrecoverableMessageHandlingException('回调数据Payload不能为空');
        }

        $account = $this->accountRepository->find($message->getAccountId());
        if (!$account) {
            throw new UnrecoverableMessageHandlingException('找不到小程序账号');
        }

        // 一些特殊情况，可能会有空的请求体提交过来
        if (!isset($payload['CreateTime'])) {
            return;
        }

        $MsgId = ArrayHelper::getValue($payload, 'MsgId', '');
        if ((bool) empty($MsgId)) {
            $MsgId = 'GEN-' . md5(Json::encode($payload));
        }

        $MsgId = strval($MsgId);

        // 重复消息的处理
        $cacheKey = ServerMessageRequestEvent::class . $MsgId;
        if ($this->cache->has($cacheKey)) {
            $this->logger->warning('缓存存在了，即为处理过了', ['cacheKey' => $cacheKey]);

            return;
        }

        // 不管事件内怎么处理，我们先自己保证存一份
        $entity = new ServerMessage();
        $entity->setAccount($account);
        $entity->setMsgId($MsgId);
        $entity->setMsgType($payload['MsgType']);
        $entity->setToUserName($payload['ToUserName']);
        $entity->setFromUserName($payload['FromUserName']);
        $entity->setCreateTime(CarbonImmutable::createFromTimestamp($payload['CreateTime'], date_default_timezone_get()));
        $entity->setRawData($payload);

        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $this->logger->warning('服务端通知重复，不处理', [
                'message' => $payload,
                'exception' => $exception,
            ]);

            return;
        } catch (ORMInvalidArgumentException $exception) {
            $this->logger->error('保存失败未知原因', [
                'message' => $payload,
                'exception' => $exception,
            ]);
        } catch (\Throwable $exception) {
            // 其他异常的话，可能会导致我们记录消息失败，但是最好还是别打断下面的流程
            $this->logger->error('记录服务端消息时发生错误', [
                'exception' => $exception,
                'message' => $entity,
            ]);
        }

        // 因为在这里我们也能拿到OpenID了，所以同时也要存库一次
        $localUser = $this->userLoader->loadUserByOpenId($payload['FromUserName']);
        if (!$localUser) {
            $localUser = new User();
            $localUser->setAccount($account);
            $localUser->setOpenId($payload['FromUserName']);
            $localUser->setLanguage(Language::zh_CN);
            $localUser->setRawData(Json::encode($payload));
            $this->entityManager->persist($localUser);
            $this->entityManager->flush();
        }

        // 既然我能拿到微信用户信息了，那么我们就存一份到主用户表
        $this->userRepository->transformToSysUser($localUser);

        // 分发事件出去让应用自己处理
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

        $this->cache->set($cacheKey, time(), 60 * 60);
    }
}
