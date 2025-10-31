<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramServerMessageBundle\Controller\Admin\ServerMessageCrudController;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

/**
 * @internal
 */
#[CoversClass(ServerMessageCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ServerMessageCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return ServerMessageCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new ServerMessageCrudController();
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID column' => ['ID'];
        yield 'Message ID column' => ['消息ID'];
        yield 'To User column' => ['接收方用户名'];
        yield 'From User column' => ['发送方用户名'];
        yield 'Message Type column' => ['消息类型'];
        yield 'Account column' => ['所属账号'];
        yield 'Create Time column' => ['创建时间'];
    }

    /**
     * 提供NEW页面字段，虽然操作被禁用，但需要提供字段数据避免基类检查失败
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 基于ServerMessageCrudController::configureFields()中的字段
        // 虽然NEW操作被禁用，但基类会检查字段配置的完整性
        yield 'msgId' => ['msgId'];
        yield 'toUserName' => ['toUserName'];
        yield 'fromUserName' => ['fromUserName'];
        yield 'msgType' => ['msgType'];
        yield 'account' => ['account'];
        yield 'createTime' => ['createTime'];
    }

    /**
     * 提供EDIT页面字段，虽然操作被禁用，但需要提供字段数据避免基类检查失败
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 基于ServerMessageCrudController::configureFields()中的字段
        // 虽然EDIT操作被禁用，但基类会检查字段配置的完整性
        yield 'msgId' => ['msgId'];
        yield 'toUserName' => ['toUserName'];
        yield 'fromUserName' => ['fromUserName'];
        yield 'msgType' => ['msgType'];
        yield 'account' => ['account'];
        yield 'createTime' => ['createTime'];
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new ServerMessageCrudController();

        $this->assertInstanceOf(ServerMessageCrudController::class, $controller);
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(ServerMessage::class, ServerMessageCrudController::getEntityFqcn());
    }

    public function testConfigureCrudSettings(): void
    {
        $controller = new ServerMessageCrudController();
        $crud = $controller->configureCrud(Crud::new());

        $this->assertInstanceOf(Crud::class, $crud);

        // 验证基本配置
        $crudAsDto = $crud->getAsDto();
        $this->assertSame('服务端消息', $crudAsDto->getEntityLabelInSingular());
        $this->assertSame('服务端消息列表', $crudAsDto->getEntityLabelInPlural());
    }

    public function testConfigureFields(): void
    {
        $controller = new ServerMessageCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields, '控制器应该配置字段');

        // 验证关键字段存在
        $fieldProperties = [];
        foreach ($fields as $field) {
            // 检查是否为 FieldInterface 实例
            if ($field instanceof FieldInterface) {
                $dto = $field->getAsDto();
                $fieldProperties[] = $dto->getProperty();
            }
        }

        $expectedFields = ['id', 'msgId', 'toUserName', 'fromUserName', 'msgType', 'account', 'rawData', 'createTime'];
        foreach ($expectedFields as $expectedField) {
            $this->assertContains($expectedField, $fieldProperties, "字段 {$expectedField} 应该被配置");
        }
    }

    public function testConfigureActions(): void
    {
        $controller = new ServerMessageCrudController();
        $actions = Actions::new();
        $configuredActions = $controller->configureActions($actions);

        $this->assertInstanceOf(Actions::class, $configuredActions);

        // 验证NEW和EDIT操作被禁用
        $actionsDto = $configuredActions->getAsDto('index');

        // 验证DETAIL操作存在
        $detailAction = $actionsDto->getAction(Crud::PAGE_INDEX, Action::DETAIL);
        $this->assertNotNull($detailAction, 'DETAIL操作应该可用');

        // 验证NEW操作被禁用（通过异常或不存在来确认）
        $newActionExists = false;
        try {
            $newAction = $actionsDto->getAction(Crud::PAGE_INDEX, Action::NEW);
            $newActionExists = (null !== $newAction);
        } catch (\InvalidArgumentException $e) {
            $newActionExists = false;
        }
        $this->assertFalse($newActionExists, 'NEW操作应该被禁用');

        // 验证EDIT操作被禁用（通过异常或不存在来确认）
        $editActionExists = false;
        try {
            $editAction = $actionsDto->getAction(Crud::PAGE_INDEX, Action::EDIT);
            $editActionExists = (null !== $editAction);
        } catch (\InvalidArgumentException $e) {
            $editActionExists = false;
        }
        $this->assertFalse($editActionExists, 'EDIT操作应该被禁用');
    }

    public function testConfigureFilters(): void
    {
        $controller = new ServerMessageCrudController();
        $filters = Filters::new();
        $configuredFilters = $controller->configureFilters($filters);

        $this->assertInstanceOf(Filters::class, $configuredFilters);
    }

    public function testFormatMsgTypeMethod(): void
    {
        $controller = new ServerMessageCrudController();

        // 使用反射来测试私有方法
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('formatMsgType');
        $method->setAccessible(true);

        // 测试已知类型
        $this->assertSame('文本消息', $method->invoke($controller, 'text'));
        $this->assertSame('图片消息', $method->invoke($controller, 'image'));
        $this->assertSame('事件消息', $method->invoke($controller, 'event'));

        // 测试未知类型
        $this->assertSame('unknown_type', $method->invoke($controller, 'unknown_type'));

        // 测试null值
        $this->assertSame('-', $method->invoke($controller, null));
    }
}
