<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\ErrorCode;

/**
 * @internal
 */
#[CoversClass(ErrorCode::class)]
#[RunTestsInSeparateProcesses]
final class ErrorCodeTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 简单的常量测试，不需要特殊的设置
    }

    public function testConstants(): void
    {
        // ErrorCode 是一个包含常量的类，不需要实例化
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
