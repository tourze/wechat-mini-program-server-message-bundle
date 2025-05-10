<?php

namespace WechatMiniProgramServerMessageBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Arrays\ArrayHelper;

/**
 * 根据相关法律法规，用户撤回同意后开发者应当主动删除用户信息。
 * 对于微信用户撤回授权信息，平台将每日一次邮件同步通知，请你及时删除附件中用户的相关授权信息（相关字段释义详见下方开发者社区文档说明）。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/framework/security.html#%E6%8E%88%E6%9D%83%E7%94%A8%E6%88%B7%E8%B5%84%E6%96%99%E5%8F%98%E6%9B%B4
 * @see https://developers.weixin.qq.com/community/develop/doc/0004aa15a00ff8ced83d720015b400
 */
class UserInfoInvokeSubscriber
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    #[AsEventListener]
    public function processInvoke(ServerMessageRequestEvent $event): void
    {
        // [▼
        //    "ToUserName" => "gh_fb9acb2703d7"
        //    "FromUserName" => "oGmqF5TVjF5qUZv0XTOEtgLCY36I"
        //    "CreateTime" => "1656661317"
        //    "MsgType" => "text"
        //    "Content" => "[Onlooker][Onlooker][Onlooker][Onlooker]"
        //    "MsgId" => "23717687884910657"
        //  ]
        $message = $event->getMessage();
        $this->logger->debug('UserInfoInvokeSubscriber收到消息', [
            'event' => $event,
            'message' => $message,
        ]);

        $Event = ArrayHelper::getValue($message, 'Event');
        if ('user_authorization_revoke' !== $Event) {
            return;
        }

        $openID = ArrayHelper::getValue($message, 'OpenID');
        if (!$openID) {
            $openID = ArrayHelper::getValue($message, 'FromUserName');
        }

        $user = $this->userLoader->loadUserByOpenId($openID);
        if (!$user) {
            return;
        }

        // RevokeInfo 用户撤回的授权信息，1:车牌号,2:地址,3:发票信息,4:蓝牙,5:麦克风,6:昵称和头像,7:摄像头,8:手机号,12:微信运动步数,13:位置信息,14:选中的图片或视频,15:选中的文件,16:邮箱地址
        // TODO 我们应该没存上面的信息？所以应该不用吧。。。
    }
}
