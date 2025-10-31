<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\SHA1;

/**
 * @internal
 */
#[CoversClass(SHA1::class)]
final class SHA1Test extends TestCase
{
    public function testGetSHA1(): void
    {
        $sha1 = new SHA1();
        $token = 'test-token';
        $timestamp = '1234567890';
        $nonce = 'test-nonce';
        $encryptMsg = 'encrypted-message';

        [$code, $signature] = $sha1->getSHA1($token, $timestamp, $nonce, $encryptMsg);

        self::assertSame(0, $code);
        self::assertIsString($signature);
        self::assertSame(40, strlen($signature)); // SHA1 signature is 40 characters
    }
}
