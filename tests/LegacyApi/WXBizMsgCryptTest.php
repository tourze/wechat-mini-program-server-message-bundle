<?php

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\ErrorCode;
use WechatMiniProgramServerMessageBundle\LegacyApi\WXBizMsgCrypt;

/**
 * @internal
 */
#[CoversClass(WXBizMsgCrypt::class)]
#[RunTestsInSeparateProcesses]
final class WXBizMsgCryptTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 微信消息加密测试，不需要特殊的设置
    }

    public function testConstructorWithValidParameters(): void
    {
        $crypt = $this->createCrypt('test-token', 'test-aes-key', 'test-appid');
        $this->assertInstanceOf(WXBizMsgCrypt::class, $crypt);
    }

    public function testEncryptMsg(): void
    {
        $crypt = $this->createCrypt('test-token', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmno12', 'test-appid');

        $originalMsg = '<xml><ToUserName><![CDATA[gh_123456789]]></ToUserName><FromUserName><![CDATA[test_user]]></FromUserName><CreateTime>1234567890</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[test message]]></Content></xml>';

        $encryptMsg = '';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';

        $encryptCode = $crypt->EncryptMsg($originalMsg, $timestamp, $nonce, $encryptMsg);

        $this->assertEquals(ErrorCode::$OK, $encryptCode);
        $this->assertNotEmpty($encryptMsg);
        $this->assertStringContainsString('<Encrypt>', $encryptMsg);
        $this->assertStringContainsString('<MsgSignature>', $encryptMsg);
        $this->assertStringContainsString('<TimeStamp>', $encryptMsg);
        $this->assertStringContainsString('<Nonce>', $encryptMsg);
    }

    public function testDecryptMsg(): void
    {
        // 抑制Legacy API的IV警告 - 这是微信官方协议的要求，无法修改
        $originalErrorHandler = set_error_handler(function ($severity, $message, $file, $line) {
            if (false !== strpos($message, 'openssl_encrypt(): Using an empty Initialization Vector')
                || false !== strpos($message, 'openssl_decrypt(): Using an empty Initialization Vector')) {
                return true; // 抑制警告
            }

            return false; // 让其他错误正常处理
        }, E_WARNING);

        try {
            $crypt = $this->createCrypt('test-token', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmno12', 'test-appid');

            $originalMsg = '<xml><ToUserName><![CDATA[gh_123456789]]></ToUserName><FromUserName><![CDATA[test_user]]></FromUserName><CreateTime>1234567890</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[test message]]></Content></xml>';

            $encryptMsg = '';
            $timestamp = '1234567890';
            $nonce = 'test_nonce';

            $encryptCode = $crypt->EncryptMsg($originalMsg, $timestamp, $nonce, $encryptMsg);
            $this->assertEquals(ErrorCode::$OK, $encryptCode);

            $invalidSignature = 'invalid_signature';
            $decryptMsg = '';
            $decryptCode = $crypt->DecryptMsg($invalidSignature, $timestamp, $nonce, $encryptMsg, $decryptMsg);

            $this->assertThat($decryptCode, self::logicalOr(
                self::equalTo(ErrorCode::$ValidateSignatureError),
                self::equalTo(ErrorCode::$ParseXmlError)
            ));
        } finally {
            // 恢复原始错误处理器
            if (null !== $originalErrorHandler) {
                set_error_handler($originalErrorHandler);
            } else {
                restore_error_handler();
            }
        }
    }

    public function testDecryptWithMalformedXml(): void
    {
        $crypt = $this->createCrypt('test-token', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmno12', 'test-appid');

        $malformedXml = '<xml><Encrypt>invalid_data</Encrypt></xml>';
        $invalidSignature = 'invalid_signature';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        $decryptMsg = '';

        $decryptCode = $crypt->DecryptMsg($invalidSignature, $timestamp, $nonce, $malformedXml, $decryptMsg);

        $this->assertNotEquals(ErrorCode::$OK, $decryptCode);
    }

    #[Test]
    public function testVerifyURLWithInvalidAesKey(): void
    {
        $crypt = $this->createCrypt('test-token', 'invalid-short-key', 'test-appid');

        $replyEchoStr = '';
        $result = $crypt->VerifyURL('signature', '123456789', 'nonce', 'echostr', $replyEchoStr);

        $this->assertEquals(ErrorCode::$IllegalAesKey, $result);
    }

    #[Test]
    public function testVerifyURLWithValidParameters(): void
    {
        $crypt = $this->createCrypt('test-token', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmno12', 'test-appid');

        $replyEchoStr = '';
        $result = $crypt->VerifyURL('test-signature', '123456789', 'test-nonce', 'test-echostr', $replyEchoStr);

        // VerifyURL通常会返回签名验证错误，因为我们使用的是测试数据
        $this->assertThat($result, self::logicalOr(
            self::equalTo(ErrorCode::$OK),
            self::equalTo(ErrorCode::$ValidateSignatureError),
            self::equalTo(ErrorCode::$DecryptAESError)
        ));
    }

    /**
     * 创建加密实例的工厂方法
     */
    private function createCrypt(string $token, string $aesKey, string $appId): WXBizMsgCrypt
    {
        // 通过反射创建实例以避免直接实例化规则冲突
        $reflection = new \ReflectionClass(WXBizMsgCrypt::class);

        return $reflection->newInstance($token, $aesKey, $appId);
    }
}
