<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Integration\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuDateValidEvent;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuQuotaNoticeEvent;
use WechatMiniProgramServerMessageBundle\EventSubscriber\ServerMessageListener;

class ServerMessageListenerTest extends TestCase
{
    private ServerMessageListener $listener;
    private MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->listener = new ServerMessageListener($this->eventDispatcher);
    }

    public function testPostPersistWithNullDirector(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getDirector')->willReturn(null);

        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->method('getAccount')->willReturn($account);

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->listener->postPersist($serverMessage);
    }

    public function testPostPersistWithNonChargeEvent(): void
    {
        $director = $this->createMock(UserInterface::class);
        $account = $this->createMock(Account::class);
        $account->method('getDirector')->willReturn($director);

        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->method('getAccount')->willReturn($account);
        $serverMessage->method('getRawData')->willReturn(['Event' => 'other_event']);

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->listener->postPersist($serverMessage);
    }

    public function testPostPersistWithQuotaNoticeEvent(): void
    {
        $director = $this->createMock(UserInterface::class);
        $account = $this->createMock(Account::class);
        $account->method('getDirector')->willReturn($director);

        $rawData = [
            'Event' => 'charge_service_quota_notify',
            'event_type' => 3,
            'spu_id' => 10000077,
            'spu_name' => '手机号快速验证组件',
            'total_quota' => 1000,
            'total_used_quota' => 1000,
        ];

        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->method('getAccount')->willReturn($account);
        $serverMessage->method('getRawData')->willReturn($rawData);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($director, $account) {
                $this->assertInstanceOf(WechatSpuQuotaNoticeEvent::class, $event);
                $this->assertSame($director, $event->getReceiver());
                $this->assertSame($account, $event->getAccount());
                $this->assertSame('10000077', $event->getSpuId());
                $this->assertSame('手机号快速验证组件', $event->getSpuName());
                $this->assertSame(1000, $event->getTotalQuota());
                $this->assertSame(1000, $event->getTotalUsedQuota());
                return true;
            }));

        $this->listener->postPersist($serverMessage);
    }

    public function testPostPersistWithDateValidEvent(): void
    {
        $director = $this->createMock(UserInterface::class);
        $account = $this->createMock(Account::class);
        $account->method('getDirector')->willReturn($director);

        $rawData = [
            'Event' => 'charge_service_quota_notify',
            'event_type' => 4,
            'spu_id' => 10000077,
            'spu_name' => '手机号快速验证组件',
            'validity_end_time' => 1693624245,
        ];

        $serverMessage = $this->createMock(ServerMessage::class);
        $serverMessage->method('getAccount')->willReturn($account);
        $serverMessage->method('getRawData')->willReturn($rawData);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($director, $account) {
                $this->assertInstanceOf(WechatSpuDateValidEvent::class, $event);
                $this->assertSame($director, $event->getReceiver());
                $this->assertSame($account, $event->getAccount());
                $this->assertSame('10000077', $event->getSpuId());
                $this->assertSame('手机号快速验证组件', $event->getSpuName());
                $this->assertSame('1693624245', $event->getValidityEndTime());
                return true;
            }));

        $this->listener->postPersist($serverMessage);
    }
}