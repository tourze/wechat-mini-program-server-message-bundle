<?php

namespace WechatMiniProgramServerMessageBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

/**
 * @extends AbstractCrudController<ServerMessage>
 */
#[AdminCrud(routePath: '/wechat-mini-program/server-message', routeName: 'wechat_mini_program_server_message')]
final class ServerMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ServerMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('服务端消息')
            ->setEntityLabelInPlural('服务端消息列表')
            ->setPageTitle('index', '服务端消息列表')
            ->setPageTitle('detail', '服务端消息详情')
            ->setPageTitle('new', '新建服务端消息')
            ->setPageTitle('edit', '编辑服务端消息')
            ->setHelp('index', '这里展示从微信服务器接收到的所有消息记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'msgId', 'toUserName', 'fromUserName', 'msgType'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本字段
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('msgId', '消息ID')
            ->setHelp('微信服务器推送的消息唯一标识')
            ->hideOnForm()
        ;

        yield TextField::new('toUserName', '接收方用户名')
            ->setHelp('消息接收方的微信用户名')
            ->hideOnForm()
        ;

        yield TextField::new('fromUserName', '发送方用户名')
            ->setHelp('消息发送方的微信用户名')
            ->hideOnForm()
        ;

        yield TextField::new('msgType', '消息类型')
            ->setHelp('消息的类型，如 text、image、voice 等')
            ->formatValue(function ($value) {
                return $this->formatMsgType($value);
            })
            ->hideOnForm()
        ;

        // 关联字段
        yield AssociationField::new('account', '所属账号')
            ->setHelp('该消息所属的微信小程序账号')
            ->hideOnForm()
        ;

        // JSON 数据字段
        yield ArrayField::new('rawData', '原始数据')
            ->setHelp('从微信服务器接收到的原始 JSON 数据')
            ->hideOnIndex()
            ->hideOnForm()
        ;

        // 时间字段
        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setTimezone('Asia/Shanghai')
            ->hideOnForm()
        ;
    }

    /**
     * 格式化消息类型显示
     */
    private function formatMsgType(?string $msgType): string
    {
        if (null === $msgType) {
            return '-';
        }

        $typeMap = [
            'text' => '文本消息',
            'image' => '图片消息',
            'voice' => '语音消息',
            'video' => '视频消息',
            'shortvideo' => '小视频消息',
            'location' => '地理位置消息',
            'link' => '链接消息',
            'event' => '事件消息',
            'miniprogrampage' => '小程序卡片消息',
        ];

        return $typeMap[$msgType] ?? $msgType;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT)  // 禁用新建和编辑功能，因为消息是从微信服务器推送的
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')  // 只有管理员可以删除
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('msgId', '消息ID'))
            ->add(TextFilter::new('toUserName', '接收方用户名'))
            ->add(TextFilter::new('fromUserName', '发送方用户名'))
            ->add(TextFilter::new('msgType', '消息类型'))
            ->add(EntityFilter::new('account', '所属账号'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
