<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 设置 LinkGenerator 的 mock
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->with(ServerMessage::class)
            ->willReturn('/admin/wechat-mini-program/server-message')
        ;

        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        // 验证实例正确创建
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testInvokeWithoutExistingMenuCreatesTopLevelMenu(): void
    {
        // 准备测试数据
        $adminMenu = self::getService(AdminMenu::class);
        $expectedUri = '/admin/wechat-mini-program/server-message';

        // 创建微信小程序菜单 mock
        $wechatMenu = $this->createMock(ItemInterface::class);

        // 创建顶级菜单 mock
        $topLevelItem = $this->createMock(ItemInterface::class);
        // 第一次调用 getChild 返回 null（用于检查菜单是否存在）
        // 第二次调用 getChild 返回创建的菜单（用于获取菜单引用）
        $topLevelItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信小程序')
            ->willReturnOnConsecutiveCalls(null, $wechatMenu)
        ;

        // 设置顶级菜单的期望行为
        $topLevelItem->expects($this->once())
            ->method('addChild')
            ->with('微信小程序')
            ->willReturn($wechatMenu)
        ;

        // 创建服务端消息菜单项 mock
        $serverMessageMenuItem = $this->createMock(ItemInterface::class);
        $serverMessageMenuItem->expects($this->once())
            ->method('setUri')
            ->with($expectedUri)
            ->willReturn($serverMessageMenuItem)
        ;
        $serverMessageMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-envelope')
            ->willReturn($serverMessageMenuItem)
        ;

        // 设置微信小程序菜单的期望行为
        $wechatMenu->expects($this->once())
            ->method('addChild')
            ->with('服务端消息')
            ->willReturn($serverMessageMenuItem)
        ;

        // 执行测试
        $adminMenu->__invoke($topLevelItem);
    }

    public function testInvokeWithExistingMenuUsesExistingTopLevelMenu(): void
    {
        // 准备测试数据
        $adminMenu = self::getService(AdminMenu::class);
        $expectedUri = '/admin/wechat-mini-program/server-message';

        // 创建已存在的微信小程序菜单 mock
        $wechatMenu = $this->createMock(ItemInterface::class);

        // 创建顶级菜单 mock
        $topLevelItem = $this->createMock(ItemInterface::class);
        $topLevelItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信小程序')
            ->willReturn($wechatMenu)
        ;

        // 顶级菜单不应该再次添加微信小程序菜单
        $topLevelItem->expects($this->never())
            ->method('addChild')
        ;

        // 创建服务端消息菜单项 mock
        $serverMessageMenuItem = $this->createMock(ItemInterface::class);
        $serverMessageMenuItem->expects($this->once())
            ->method('setUri')
            ->with($expectedUri)
            ->willReturn($serverMessageMenuItem)
        ;
        $serverMessageMenuItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-envelope')
            ->willReturn($serverMessageMenuItem)
        ;

        // 设置微信小程序菜单的期望行为
        $wechatMenu->expects($this->once())
            ->method('addChild')
            ->with('服务端消息')
            ->willReturn($serverMessageMenuItem)
        ;

        // 执行测试
        $adminMenu->__invoke($topLevelItem);
    }
}
