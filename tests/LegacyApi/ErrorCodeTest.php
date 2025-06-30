<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\ErrorCode;

final class ErrorCodeTest extends TestCase
{
    public function testConstants(): void
    {
        self::assertSame(0, ErrorCode::$OK);
        self::assertSame(-40001, ErrorCode::$ValidateSignatureError);
        self::assertSame(-40002, ErrorCode::$ParseXmlError);
        self::assertSame(-40003, ErrorCode::$ComputeSignatureError);
        self::assertSame(-40004, ErrorCode::$IllegalAesKey);
        self::assertSame(-40005, ErrorCode::$ValidateCorpidError);
        self::assertSame(-40006, ErrorCode::$EncryptAESError);
        self::assertSame(-40007, ErrorCode::$DecryptAESError);
        self::assertSame(-40008, ErrorCode::$IllegalBuffer);
        self::assertSame(-40009, ErrorCode::$EncodeBase64Error);
        self::assertSame(-40010, ErrorCode::$DecodeBase64Error);
        self::assertSame(-40011, ErrorCode::$GenReturnXmlError);
    }
}