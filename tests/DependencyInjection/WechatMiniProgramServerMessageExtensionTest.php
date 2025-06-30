<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatMiniProgramServerMessageBundle\DependencyInjection\WechatMiniProgramServerMessageExtension;

final class WechatMiniProgramServerMessageExtensionTest extends TestCase
{
    private WechatMiniProgramServerMessageExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new WechatMiniProgramServerMessageExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);

        // 测试服务是否被加载
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramServerMessageBundle\Service\AttributeControllerLoader'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramServerMessageBundle\MessageHandler\ServerPayloadReceivedHandler'));
    }
}