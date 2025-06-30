<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\XMLParse;

final class XMLParseTest extends TestCase
{
    private XMLParse $xmlParse;

    protected function setUp(): void
    {
        $this->xmlParse = new XMLParse();
    }

    public function testExtractAndGenerate(): void
    {
        $xmlStr = '<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[encrypted-data]]></Encrypt></xml>';
        
        [$code, $encrypt] = $this->xmlParse->extract($xmlStr);
        self::assertSame(0, $code);
        self::assertSame('encrypted-data', $encrypt);

        $generatedXml = $this->xmlParse->generate($encrypt, 'signature', 'timestamp', 'nonce');
        self::assertStringContainsString('<Encrypt><![CDATA[encrypted-data]]></Encrypt>', $generatedXml);
        self::assertStringContainsString('<MsgSignature><![CDATA[signature]]></MsgSignature>', $generatedXml);
        self::assertStringContainsString('<TimeStamp>timestamp</TimeStamp>', $generatedXml);
        self::assertStringContainsString('<Nonce><![CDATA[nonce]]></Nonce>', $generatedXml);
    }
}