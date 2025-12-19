<?php

namespace WechatMiniProgramServerMessageBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\EventSubscriber\ServerMessageListener;

/**
 * @internal
 */
#[CoversClass(ServerMessageListener::class)]
#[RunTestsInSeparateProcesses]
final class ServerMessageListenerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试不需要特殊设置
    }

    public function testListenerCanBeInstantiatedFromContainer(): void
    {
        $listener = self::getService(ServerMessageListener::class);

        $this->assertInstanceOf(ServerMessageListener::class, $listener);
    }

    #[Test]
    public function testPostPersistWithNullAccount(): void
    {
        $listener = self::getService(ServerMessageListener::class);

        $message = new ServerMessage();
        $message->setAccount(null);

        // 当 account 为 null 时，不应抛出异常
        $listener->postPersist($message);

        // 验证方法可以正常执行
        $this->assertTrue(true);
    }

    #[Test]
    public function testListenerHasCorrectPostPersistMethodSignature(): void
    {
        $listener = self::getService(ServerMessageListener::class);

        $reflection = new \ReflectionClass($listener);
        $this->assertTrue($reflection->hasMethod('postPersist'));

        $method = $reflection->getMethod('postPersist');
        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);

        $firstParameter = $parameters[0];
        $this->assertSame('object', $firstParameter->getName());

        $type = $firstParameter->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertSame(ServerMessage::class, $type->getName());
        $this->assertFalse($type->allowsNull());
    }

    #[Test]
    public function testListenerHasCorrectReturnType(): void
    {
        $listener = self::getService(ServerMessageListener::class);

        $reflection = new \ReflectionClass($listener);
        $method = $reflection->getMethod('postPersist');

        $returnType = $method->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame('void', $returnType->getName());
    }

    #[Test]
    public function testEventDispatcherIsInjected(): void
    {
        $listener = self::getService(ServerMessageListener::class);

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('eventDispatcher');

        $eventDispatcher = $property->getValue($listener);
        $this->assertInstanceOf(EventDispatcherInterface::class, $eventDispatcher);
    }
}
