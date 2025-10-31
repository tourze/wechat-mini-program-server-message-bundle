<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Event\WechatSpuDateValidEvent;

/**
 * @internal
 */
#[CoversClass(WechatSpuDateValidEvent::class)]
final class WechatSpuDateValidEventTest extends AbstractEventTestCase
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

        // Mock具体类Account是合理的，因为：
        // 1. Account是外部依赖包的实体类，不属于当前包
        // 2. 事件测试只需要验证事件数据的传递，不依赖Account的具体实现
        // 3. Account包含数据库映射，Mock避免了数据库依赖
        $account = $this->createMock(Account::class);
        $event->setAccount($account);
        self::assertSame($account, $event->getAccount());
    }
}
