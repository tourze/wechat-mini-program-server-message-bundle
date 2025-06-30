<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WechatMiniProgramServerMessageBundle\WechatMiniProgramServerMessageBundle;

class WechatMiniProgramServerMessageBundleTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new WechatMiniProgramServerMessageBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
        $this->assertInstanceOf(WechatMiniProgramServerMessageBundle::class, $bundle);
    }

    public function testBundleName(): void
    {
        $bundle = new WechatMiniProgramServerMessageBundle();
        $this->assertEquals('WechatMiniProgramServerMessageBundle', $bundle->getName());
    }

    public function testBundleNamespace(): void
    {
        $bundle = new WechatMiniProgramServerMessageBundle();
        $this->assertEquals('WechatMiniProgramServerMessageBundle', $bundle->getNamespace());
    }
}