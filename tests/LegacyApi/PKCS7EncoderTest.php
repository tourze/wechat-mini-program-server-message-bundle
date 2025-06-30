<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\PKCS7Encoder;

final class PKCS7EncoderTest extends TestCase
{
    private PKCS7Encoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PKCS7Encoder();
    }

    public function testEncodeAndDecode(): void
    {
        $originalText = 'test message';
        $encoded = $this->encoder->encode($originalText);
        $decoded = $this->encoder->decode($encoded);

        self::assertStringStartsWith($originalText, $decoded);
    }

    public function testBlockSize(): void
    {
        $originalText = 'test';
        $encoded = $this->encoder->encode($originalText);
        self::assertSame(0, strlen($encoded) % 32);
    }
}