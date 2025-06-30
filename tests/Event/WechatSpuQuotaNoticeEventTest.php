<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuQuotaNoticeEvent;

final class WechatSpuQuotaNoticeEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new WechatSpuQuotaNoticeEvent();

        $event->setTotalQuota(100);
        self::assertSame(100, $event->getTotalQuota());

        $event->setTotalUsedQuota(50);
        self::assertSame(50, $event->getTotalUsedQuota());

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