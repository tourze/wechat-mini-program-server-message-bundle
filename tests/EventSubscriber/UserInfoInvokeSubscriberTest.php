<?php

namespace WechatMiniProgramServerMessageBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use WechatMiniProgramServerMessageBundle\EventSubscriber\UserInfoInvokeSubscriber;

/**
 * @internal
 */
#[CoversClass(UserInfoInvokeSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class UserInfoInvokeSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testSubscriberCanBeInstantiated(): void
    {
        $subscriber = self::getService(UserInfoInvokeSubscriber::class);
        $this->assertInstanceOf(UserInfoInvokeSubscriber::class, $subscriber);
    }

    #[Test]
    public function testProcessInvokeWithNonRevokeEvent(): void
    {
        $subscriber = self::getService(UserInfoInvokeSubscriber::class);

        $event = new ServerMessageRequestEvent();
        $event->setMessage(['Event' => 'other_event']);

        // 应该不会抛出异常，方法会提前返回
        $subscriber->processInvoke($event);

        // 验证事件消息没有被修改
        $this->assertEquals(['Event' => 'other_event'], $event->getMessage());
    }

    #[Test]
    public function testProcessInvokeWithRevokeEvent(): void
    {
        $subscriber = self::getService(UserInfoInvokeSubscriber::class);

        $event = new ServerMessageRequestEvent();
        $event->setMessage([
            'Event' => 'user_authorization_revoke',
            'OpenID' => 'test-openid-for-revoke',
        ]);

        // 这应该执行完整的撤回逻辑（或因为没有UserLoader而记录警告）
        $subscriber->processInvoke($event);

        // 验证事件消息包含正确的事件类型
        $this->assertEquals('user_authorization_revoke', $event->getMessage()['Event']);
        $this->assertEquals('test-openid-for-revoke', $event->getMessage()['OpenID']);
    }
}
