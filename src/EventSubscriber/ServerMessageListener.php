<?php

namespace WechatMiniProgramServerMessageBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuDateValidEvent;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuQuotaNoticeEvent;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/charge/intro.html#%E6%9F%A5%E7%9C%8B%E7%94%A8%E9%87%8F
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/charge/callback/charge_mp_service_validity_notify.html
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ServerMessage::class)]
class ServerMessageListener
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function postPersist(ServerMessage $object): void
    {
        // 没有负责人，不处理了
        if (!$object->getAccount()->getDirector()) {
            return;
        }

        if ($object->getRawData()['Event'] ?? '' !== 'charge_service_quota_notify') {
            return;
        }

        if (3 === $object->getRawData()['event_type']) {
            // {"ToUserName":"gh_24b0688b9fda","FromUserName":"oeg5b5PFuO_A9Ax3cBxajvn6q41Y","CreateTime":1693624245,"MsgType":"event","Event":"charge_service_quota_notify","event_type":3,"spu_id":10000077,"spu_name":"手机号快速验证组件","total_quota":1000,"total_used_quota":1000,"appid":"wxb26a710e583b05dc"}
            $event = new WechatSpuQuotaNoticeEvent();
            $event->setSender(SystemUser::instance());
            $event->setReceiver($object->getAccount()->getDirector());
            $event->setAccount($object->getAccount());
            $event->setSpuId($object->getRawData()['spu_id']);
            $event->setSpuName($object->getRawData()['spu_name']);
            $event->setTotalQuota($object->getRawData()['total_quota']);
            $event->setTotalUsedQuota($object->getRawData()['total_used_quota']);
            $this->eventDispatcher->dispatch($event);
        }

        if (4 === $object->getRawData()['event_type']) {
            $event = new WechatSpuDateValidEvent();
            $event->setSender(SystemUser::instance());
            $event->setReceiver($object->getAccount()->getDirector());
            $event->setAccount($object->getAccount());
            $event->setSpuId($object->getRawData()['spu_id']);
            $event->setSpuName($object->getRawData()['spu_name']);
            $event->setValidityEndTime($object->getRawData()['validity_end_time']);
            $this->eventDispatcher->dispatch($event);
        }
    }
}
