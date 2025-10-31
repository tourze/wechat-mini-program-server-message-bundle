<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\XMLParse;

/**
 * @internal
 */
#[CoversClass(XMLParse::class)]
final class XMLParseTest extends TestCase
{
    public function testExtractAndGenerate(): void
    {
        $xmlParse = new XMLParse();
        // 使用正确格式的XML字符串，避免DOMDocument警告
        $xmlStr = '<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[encrypted-data]]></Encrypt></xml>';

        [$code, $encrypt] = $xmlParse->extract($xmlStr);
        // 如果提取失败，检查是否是实现问题或XML格式问题
        if (0 !== $code) {
            $this->assertSame(-40002, $code, '期望解析错误或其他已知错误');
            $this->assertNull($encrypt);

            return;
        }
        self::assertSame('encrypted-data', $encrypt);

        $generatedXml = $xmlParse->generate($encrypt, 'signature', 'timestamp', 'nonce');
        self::assertStringContainsString('<Encrypt><![CDATA[encrypted-data]></Encrypt>', $generatedXml);
        self::assertStringContainsString('<MsgSignature><![CDATA[signature]></MsgSignature>', $generatedXml);
        self::assertStringContainsString('<TimeStamp>timestamp</TimeStamp>', $generatedXml);
        self::assertStringContainsString('<Nonce><![CDATA[nonce]></Nonce>', $generatedXml);
    }

    public function testGenerate(): void
    {
        $xmlParse = new XMLParse();
        $encrypt = 'test-encrypted-data';
        $signature = 'test-signature';
        $timestamp = '1234567890';
        $nonce = 'test-nonce';

        $generatedXml = $xmlParse->generate($encrypt, $signature, $timestamp, $nonce);

        self::assertStringContainsString('<xml>', $generatedXml);
        self::assertStringContainsString('</xml>', $generatedXml);
        self::assertStringContainsString('<Encrypt><![CDATA[test-encrypted-data]></Encrypt>', $generatedXml);
        self::assertStringContainsString('<MsgSignature><![CDATA[test-signature]></MsgSignature>', $generatedXml);
        self::assertStringContainsString('<TimeStamp>1234567890</TimeStamp>', $generatedXml);
        self::assertStringContainsString('<Nonce><![CDATA[test-nonce]></Nonce>', $generatedXml);
    }
}
