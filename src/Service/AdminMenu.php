<?php

namespace WechatMiniProgramServerMessageBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 检查并创建顶级菜单
        if (null === $item->getChild('微信小程序')) {
            $item->addChild('微信小程序');
        }

        // 获取菜单引用
        $menu = $item->getChild('微信小程序');
        if (null === $menu) {
            return;
        }

        // 添加子菜单项
        $menu->addChild('服务端消息')
            ->setUri($this->linkGenerator->getCurdListPage(ServerMessage::class))
            ->setAttribute('icon', 'fas fa-envelope')
        ;
    }
}
