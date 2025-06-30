<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuDateValidEvent;

final class WechatSpuDateValidEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new WechatSpuDateValidEvent();

        $event->setValidityEndTime('2025-12-31');
        self::assertSame('2025-12-31', $event->getValidityEndTime());

        $event->setSpuId('spu-123');
        self::assertSame('spu-123', $event->getSpuId());

        $event->setSpuName('Test SPU');
        self::assertSame('Test SPU', $event->getSpuName());

        /** @var MockObject&Account $account */
        $account = $this->createMock(Account::class);
        $event->setAccount($account);
        self::assertSame($account, $event->getAccount());
    }
}