<?php

namespace WechatMiniProgramServerMessageBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuDateValidEvent;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuQuotaNoticeEvent;
use WechatMiniProgramServerMessageBundle\EventSubscriber\ServerMessageListener;

/**
 * @internal
 */
#[CoversClass(ServerMessageListener::class)]
final class ServerMessageListenerTest extends TestCase
{
    public function testListenerCanBeInstantiated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $listener = new ServerMessageListener($eventDispatcher);

        $this->assertInstanceOf(ServerMessageListener::class, $listener);
    }

    #[Test]
    public function testPostPersistWithNullAccount(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $listener = new ServerMessageListener($eventDispatcher);
        $message = new ServerMessage();

        $listener->postPersist($message);
    }

    #[Test]
    public function testPostPersistWithNullDirector(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getDirector')->willReturn(null);

        $message = new ServerMessage();
        $message->setAccount($account);

        $listener = new ServerMessageListener($eventDispatcher);
        $listener->postPersist($message);
    }

    #[Test]
    public function testPostPersistWithNonChargeEvent(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $director = $this->createMock(SystemUser::class);
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getDirector')->willReturn($director);

        $message = new ServerMessage();
        $message->setAccount($account);
        $message->setRawData(['Event' => 'other_event']);

        $listener = new ServerMessageListener($eventDispatcher);
        $listener->postPersist($message);
    }

    #[Test]
    public function testPostPersistWithQuotaNoticeEvent(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch')
            ->with(self::isInstanceOf(WechatSpuQuotaNoticeEvent::class))
        ;

        $director = $this->createMock(SystemUser::class);
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getDirector')->willReturn($director);

        $message = new ServerMessage();
        $message->setAccount($account);
        $message->setRawData([
            'Event' => 'charge_service_quota_notify',
            'event_type' => 3,
            'spu_id' => 10000077,
            'spu_name' => 'test-spu',
            'total_quota' => 1000,
            'total_used_quota' => 900,
        ]);

        $listener = new ServerMessageListener($eventDispatcher);
        $listener->postPersist($message);
    }

    #[Test]
    public function testPostPersistWithDateValidEvent(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch')
            ->with(self::isInstanceOf(WechatSpuDateValidEvent::class))
        ;

        $director = $this->createMock(SystemUser::class);
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getDirector')->willReturn($director);

        $message = new ServerMessage();
        $message->setAccount($account);
        $message->setRawData([
            'Event' => 'charge_service_quota_notify',
            'event_type' => 4,
            'spu_id' => 10000077,
            'spu_name' => 'test-spu',
            'validity_end_time' => 1693624245,
        ]);

        $listener = new ServerMessageListener($eventDispatcher);
        $listener->postPersist($message);
    }
}
