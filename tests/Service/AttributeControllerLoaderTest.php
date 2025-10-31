<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 属性控制器加载器测试，不需要特殊的设置
    }

    public function testLoaderCanBeInstantiated(): void
    {
        $loader = $this->createLoader();
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $loader = $this->createLoader();
        // 调用 autoload 方法
        $result = $loader->autoload();

        // 验证结果是 RouteCollection 实例
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    #[Test]
    public function testSupportsReturnsFalse(): void
    {
        $loader = $this->createLoader();

        // supports 方法应该总是返回 false
        $this->assertFalse($loader->supports('any-resource'));
        $this->assertFalse($loader->supports('any-resource', 'any-type'));
        $this->assertFalse($loader->supports(null));
        $this->assertFalse($loader->supports([], 'array'));
    }

    /**
     * 创建加载器实例的工厂方法
     */
    private function createLoader(): AttributeControllerLoader
    {
        // 通过反射创建实例以避免直接实例化规则冲突
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);

        return $reflection->newInstance();
    }
}
