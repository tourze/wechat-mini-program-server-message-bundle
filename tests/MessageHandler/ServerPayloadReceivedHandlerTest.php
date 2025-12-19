<?php

namespace WechatMiniProgramServerMessageBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use WechatMiniProgramServerMessageBundle\MessageHandler\ServerPayloadReceivedHandler;

/**
 * @internal
 */
#[CoversClass(ServerPayloadReceivedHandler::class)]
#[RunTestsInSeparateProcesses]
final class ServerPayloadReceivedHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 消息处理器测试，不需要特殊的设置
    }

    public function testHandlerCanBeInstantiatedFromContainer(): void
    {
        $handler = self::getService(ServerPayloadReceivedHandler::class);

        $this->assertInstanceOf(ServerPayloadReceivedHandler::class, $handler);
    }

    public function testHandlerHasCorrectInvokeMethodSignature(): void
    {
        $handler = self::getService(ServerPayloadReceivedHandler::class);

        $reflection = new \ReflectionClass($handler);
        $this->assertTrue($reflection->hasMethod('__invoke'));

        $method = $reflection->getMethod('__invoke');
        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);

        $firstParameter = $parameters[0];
        $this->assertSame('message', $firstParameter->getName());

        $type = $firstParameter->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertSame(ServerPayloadReceivedMessage::class, $type->getName());
        $this->assertFalse($type->allowsNull());
    }

    public function testHandlerHasRequiredReturnType(): void
    {
        $handler = self::getService(ServerPayloadReceivedHandler::class);

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('__invoke');

        $returnType = $method->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame('void', $returnType->getName());
    }
}
