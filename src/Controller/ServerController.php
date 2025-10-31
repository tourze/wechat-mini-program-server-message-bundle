<?php

namespace WechatMiniProgramServerMessageBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatHelper\XML;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\LegacyApi\WXBizMsgCrypt;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use Yiisoft\Json\Json;

final class ServerController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/framework/server-ability/message-push.html
     */
    #[Route(path: '/wechat/mini-program/server/{appId}', name: 'wechat_mini_program_server', methods: ['GET', 'POST'])]
    public function __invoke(
        string $appId,
        Request $request,
        AccountRepository $accountRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
    ): Response {
        $account = $accountRepository->findOneBy(['appId' => $appId]);
        if (null === $account) {
            $logger->error('找不到小程序', ['appId' => $appId]);
            throw new NotFoundHttpException('找不到小程序');
        }
        $this->entityManager->getUnitOfWork()->markReadOnly($account);

        if ('GET' === $request->getMethod()) {
            return $this->handleGetRequest($request, $account, $logger);
        }

        return $this->handlePostRequest($request, $account, $messageBus, $logger);
    }

    private function handleGetRequest(Request $request, Account $account, LoggerInterface $logger): Response
    {
        $signature = $request->query->get('signature');
        $timestamp = $request->query->get('timestamp');
        $nonce = $request->query->get('nonce');

        $tmpArr = [
            $account->getToken(),
            $timestamp,
            $nonce,
        ];
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode($tmpArr));

        if ($tmpStr === $signature) {
            return new Response((string) $request->query->get('echostr'));
        }

        $logger->warning('服务端消息校验失败', [
            'tmpStr' => $tmpStr,
            'signature' => $signature,
            'query' => $request->query->all(),
            'account' => $account,
        ]);

        return new Response('error');
    }

    private function handlePostRequest(Request $request, Account $account, MessageBusInterface $messageBus, LoggerInterface $logger): Response
    {
        $msg = $this->decryptMessage($request, $account, $logger);
        if (null === $msg) {
            return new Response('error');
        }

        $json = $this->parseMessage($msg);

        $logger->info('收到小程序服务端数据', [
            '原始数据' => $request->getContent(),
            '解密后数据' => $msg,
            '解密后数据json' => $json,
        ]);

        $message = new ServerPayloadReceivedMessage();
        $message->setAccountId((string) $account->getId());
        $message->setPayload($json);
        $messageBus->dispatch($message);

        return new Response('');
    }

    private function decryptMessage(Request $request, Account $account, LoggerInterface $logger): ?string
    {
        try {
            $msg = '';
            $token = $account->getToken();
            if (null === $token) {
                $logger->error('账号 Token 为空');

                return null;
            }
            $crypt = new WXBizMsgCrypt($token, $account->getAppSecret(), $account->getAppId());
            $msgSignature = $request->query->get('msg_signature');
            $timestamp = $request->query->get('timestamp');
            $nonce = $request->query->get('nonce');

            if (!is_string($msgSignature) || !is_string($nonce)) {
                $logger->error('缺少必要的参数');

                return null;
            }

            $timestampStr = is_string($timestamp) ? $timestamp : null;

            $errCode = $crypt->DecryptMsg(
                $msgSignature,
                $timestampStr,
                $nonce,
                $request->getContent(),
                $msg
            );
        } catch (\Throwable $exception) {
            $logger->error('解密出错', ['exception' => $exception]);

            return null;
        }

        if ('' === $msg) {
            $msg = $request->getContent();
            $errCode = 0;
        }

        if (0 !== $errCode) {
            return null;
        }

        return $msg;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseMessage(string $msg): array
    {
        if (json_validate($msg)) {
            return Json::decode($msg);
        }

        return XML::parse($msg);
    }
}
