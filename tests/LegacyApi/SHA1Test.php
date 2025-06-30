<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\SHA1;

final class SHA1Test extends TestCase
{
    private SHA1 $sha1;

    protected function setUp(): void
    {
        $this->sha1 = new SHA1();
    }

    public function testGetSHA1(): void
    {
        $token = 'test-token';
        $timestamp = '1234567890';
        $nonce = 'test-nonce';
        $encryptMsg = 'encrypted-message';

        [$code, $signature] = $this->sha1->getSHA1($token, $timestamp, $nonce, $encryptMsg);
        
        self::assertSame(0, $code);
        self::assertIsString($signature);
        self::assertSame(40, strlen($signature)); // SHA1 signature is 40 characters
    }
}