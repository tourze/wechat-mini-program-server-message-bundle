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
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\LegacyApi\WXBizMsgCrypt;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use Yiisoft\Json\Json;

class ServerController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
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
        // $logger->info('开始处理1', ['appId' => $appId]);
        // 进入的话，必然是小程序应用了
        $account = $accountRepository->findOneBy(['appId' => $appId]);
        if (null === $account) {
            $logger->error('找不到小程序', ['appId' => $appId]);
            throw new NotFoundHttpException('找不到小程序');
        }
        $this->entityManager->getUnitOfWork()->markReadOnly($account);

        // $logger->info('开始处理2', ['appId' => $appId]);

        // GET请求一般都是验证消息
        if ('GET' === $request->getMethod()) {
            $signature = $request->query->get('signature');
            $timestamp = $request->query->get('timestamp');
            $nonce = $request->query->get('nonce');

            $tmpArr = [
                $account->getToken(),
                $timestamp,
                $nonce,
            ];
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);

            if ($tmpStr === $signature) {
                return new Response($request->query->get('echostr'));
            }
            $logger->warning('服务端消息校验失败', [
                'tmpStr' => $tmpStr,
                'signature' => $signature,
                'query' => $request->query->all(),
                'account' => $account,
            ]);

            return new Response('error');
        }

        // $logger->info('开始处理3', ['appId' => $appId]);

        try {
            $msg = '';
            $crypt = new WXBizMsgCrypt($account->getToken(), $account->getAppSecret(), $account->getAppId());
            $errCode = $crypt->DecryptMsg(
                $request->query->get('msg_signature'),
                $request->query->get('timestamp'),
                $request->query->get('nonce'),
                $request->getContent(),
                $msg
            );
        } catch (\Throwable $exception) {
            $logger->error('解密出错', ['exception' => $exception]);

            return new Response('error');
        }
        if (!$msg) {
            $msg = $request->getContent();
            $errCode = 0;
        }

        // $logger->info('开始处理4', ['appId' => $appId, 'msg' => $msg]);

        if ((bool) json_validate($msg)) {
            $json = Json::decode($msg);
        } else {
            $json = XML::parse($msg);
        }

        $logger->info('收到小程序服务端数据', [
            '原始数据' => $request->getContent(),
            '解密后数据' => $msg,
            '解密后数据json' => $json,
            'errCode' => $errCode,
        ]);

        if (0 !== $errCode) {
            return new Response('error');
        }

        $message = new ServerPayloadReceivedMessage();
        $message->setAccountId((string) $account->getId());
        $message->setPayload($json);
        $messageBus->dispatch($message);

        return new Response('');
    }
}
