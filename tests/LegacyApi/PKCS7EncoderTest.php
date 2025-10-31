<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\PKCS7Encoder;

/**
 * @internal
 */
#[CoversClass(PKCS7Encoder::class)]
final class PKCS7EncoderTest extends TestCase
{
    public function testEncodeAndDecode(): void
    {
        $encoder = new PKCS7Encoder();
        $originalText = 'test message';
        $encoded = $encoder->encode($originalText);
        $decoded = $encoder->decode($encoded);

        self::assertStringStartsWith($originalText, $decoded);
    }

    public function testBlockSize(): void
    {
        $encoder = new PKCS7Encoder();
        $originalText = 'test';
        $encoded = $encoder->encode($originalText);
        self::assertSame(0, strlen($encoded) % 32);
    }

    public function testDecode(): void
    {
        $encoder = new PKCS7Encoder();
        $originalText = 'test message';
        $encoded = $encoder->encode($originalText);
        $decoded = $encoder->decode($encoded);

        self::assertSame($originalText, $decoded);
    }
}
