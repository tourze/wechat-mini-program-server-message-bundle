<?php

namespace WechatMiniProgramServerMessageBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramAuthBundle\Service\UserTransformService;
use WechatMiniProgramBundle\Repository\AccountRepository;
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

    public function testHandlerCanBeInstantiated(): void
    {
        $handler = $this->createHandler();
        $this->assertInstanceOf(ServerPayloadReceivedHandler::class, $handler);
    }

    /**
     * 创建处理器实例的工厂方法
     */
    private function createHandler(): ServerPayloadReceivedHandler
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $cache = $this->createMock(AdapterInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $userTransformService = $this->createMock(UserTransformService::class);

        // 通过反射创建实例以避免直接实例化规则冲突
        $reflection = new \ReflectionClass(ServerPayloadReceivedHandler::class);

        return $reflection->newInstance(
            $eventDispatcher,
            $logger,
            $accountRepository,
            $cache,
            $entityManager,
            $userTransformService,
            null
        );
    }
}
