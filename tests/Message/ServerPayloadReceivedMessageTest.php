<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;

/**
 * @internal
 */
#[CoversClass(ServerPayloadReceivedMessage::class)]
final class ServerPayloadReceivedMessageTest extends TestCase
{
    public function testMessage(): void
    {
        $payload = ['test' => 'payload'];
        $accountId = 'test-account-id';

        $message = new ServerPayloadReceivedMessage();

        $message->setPayload($payload);
        self::assertSame($payload, $message->getPayload());

        $message->setAccountId($accountId);
        self::assertSame($accountId, $message->getAccountId());
    }
}
