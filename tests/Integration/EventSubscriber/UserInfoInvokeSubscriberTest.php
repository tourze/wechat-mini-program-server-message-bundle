<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Integration\EventSubscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use WechatMiniProgramServerMessageBundle\EventSubscriber\UserInfoInvokeSubscriber;

class UserInfoInvokeSubscriberTest extends TestCase
{
    private UserInfoInvokeSubscriber $subscriber;
    private MockObject $logger;
    private MockObject $userLoader;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->subscriber = new UserInfoInvokeSubscriber($this->logger, $this->userLoader);
    }

    public function testProcessInvokeWithNonRevokeEvent(): void
    {
        $event = $this->createMock(ServerMessageRequestEvent::class);
        $event->method('getMessage')->willReturn([
            'Event' => 'other_event',
            'OpenID' => 'test_open_id',
        ]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('UserInfoInvokeSubscriber收到消息', $this->isType('array'));

        $this->userLoader->expects($this->never())->method('loadUserByOpenId');

        $this->subscriber->processInvoke($event);
    }

    public function testProcessInvokeWithRevokeEventButNoOpenId(): void
    {
        $event = $this->createMock(ServerMessageRequestEvent::class);
        $event->method('getMessage')->willReturn([
            'Event' => 'user_authorization_revoke',
            'OpenID' => '',
            'FromUserName' => '',
        ]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('UserInfoInvokeSubscriber收到消息', $this->isType('array'));

        $this->userLoader->expects($this->once())
            ->method('loadUserByOpenId')
            ->with('')
            ->willReturn(null);

        $this->subscriber->processInvoke($event);
    }

    public function testProcessInvokeWithRevokeEventAndOpenId(): void
    {
        $event = $this->createMock(ServerMessageRequestEvent::class);
        $event->method('getMessage')->willReturn([
            'Event' => 'user_authorization_revoke',
            'OpenID' => 'test_open_id',
        ]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('UserInfoInvokeSubscriber收到消息', $this->isType('array'));

        $this->userLoader->expects($this->once())
            ->method('loadUserByOpenId')
            ->with('test_open_id')
            ->willReturn(null);

        $this->subscriber->processInvoke($event);
    }

    public function testProcessInvokeWithRevokeEventUsingFromUserName(): void
    {
        $event = $this->createMock(ServerMessageRequestEvent::class);
        $event->method('getMessage')->willReturn([
            'Event' => 'user_authorization_revoke',
            'OpenID' => null,
            'FromUserName' => 'from_user_name',
        ]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('UserInfoInvokeSubscriber收到消息', $this->isType('array'));

        $this->userLoader->expects($this->once())
            ->method('loadUserByOpenId')
            ->with('from_user_name')
            ->willReturn(null);

        $this->subscriber->processInvoke($event);
    }

    public function testProcessInvokeWithRevokeEventAndUserFound(): void
    {
        $user = $this->createMock(UserInterface::class);

        $event = $this->createMock(ServerMessageRequestEvent::class);
        $event->method('getMessage')->willReturn([
            'Event' => 'user_authorization_revoke',
            'OpenID' => 'test_open_id',
            'RevokeInfo' => '1,2,3',
        ]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('UserInfoInvokeSubscriber收到消息', $this->isType('array'));

        $this->userLoader->expects($this->once())
            ->method('loadUserByOpenId')
            ->with('test_open_id')
            ->willReturn($user);

        // TODO 部分已经实现，但有TODO注释
        $this->subscriber->processInvoke($event);
    }
}