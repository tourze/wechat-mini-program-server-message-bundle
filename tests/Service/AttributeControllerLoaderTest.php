<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\RouteCollection;
use WechatMiniProgramServerMessageBundle\Service\AttributeControllerLoader;

final class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;
    private MockObject&AttributeClassLoader $controllerLoader;

    protected function setUp(): void
    {
        $this->controllerLoader = $this->createMock(AttributeClassLoader::class);
        $this->loader = new AttributeControllerLoader($this->controllerLoader);
    }

    public function testAutoload(): void
    {
        $routeCollection = new RouteCollection();
        
        $this->controllerLoader->expects(self::once())
            ->method('load')
            ->willReturn($routeCollection);
        
        $result = $this->loader->autoload();
        
        self::assertInstanceOf(RouteCollection::class, $result);
    }
}