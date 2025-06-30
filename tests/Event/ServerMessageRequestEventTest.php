<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;

final class ServerMessageRequestEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new ServerMessageRequestEvent();
        
        $message = ['test' => 'message'];
        $event->setMessage($message);
        self::assertSame($message, $event->getMessage());

        /** @var MockObject&Account $account */
        $account = $this->createMock(Account::class);
        $event->setAccount($account);
        self::assertSame($account, $event->getAccount());

        /** @var MockObject&UserInterface $wechatUser */
        $wechatUser = $this->createMock(UserInterface::class);
        $event->setWechatUser($wechatUser);
        self::assertSame($wechatUser, $event->getWechatUser());
    }
}