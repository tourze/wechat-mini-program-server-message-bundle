<?php

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\ErrorCode;
use WechatMiniProgramServerMessageBundle\LegacyApi\WXBizMsgCrypt;

class WXBizMsgCryptTest extends TestCase
{
    private string $token = 'test_token';
    private string $appId = 'wxd5e58c3e953de855';
    private string $encodingAesKey = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG';
    
    // 测试初始化
    public function testConstructorWithValidParameters(): void
    {
        $crypt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        $this->assertInstanceOf(WXBizMsgCrypt::class, $crypt);
    }
    
    // 测试加密和解密正常流程
    public function testEncryptAndDecryptWithValidParameters(): void
    {
        $crypt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        // 原始消息
        $originalMsg = '<xml><ToUserName><![CDATA[gh_123456789]]></ToUserName><FromUserName><![CDATA[test_user]]></FromUserName><CreateTime>1234567890</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[test message]]></Content></xml>';
        
        // 加密消息
        $encryptMsg = '';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        
        $encryptCode = $crypt->EncryptMsg($originalMsg, $timestamp, $nonce, $encryptMsg);
        
        // 验证加密成功
        $this->assertEquals(ErrorCode::$OK, $encryptCode);
        $this->assertNotEmpty($encryptMsg);
        $this->assertStringContainsString('<Encrypt>', $encryptMsg);
        $this->assertStringContainsString('<MsgSignature>', $encryptMsg);
        $this->assertStringContainsString('<TimeStamp>', $encryptMsg);
        $this->assertStringContainsString('<Nonce>', $encryptMsg);
        
        // 从加密消息中提取签名等信息
        preg_match('/<MsgSignature><!\[CDATA\[(.+?)\]\]><\/MsgSignature>/', $encryptMsg, $matches);
        $msgSignature = $matches[1];
        
        // 解密消息
        $decryptMsg = '';
        $decryptCode = $crypt->DecryptMsg($msgSignature, $timestamp, $nonce, $encryptMsg, $decryptMsg);
        
        // 验证解密成功
        $this->assertEquals(ErrorCode::$OK, $decryptCode);
        $this->assertEquals($originalMsg, $decryptMsg);
    }
    
    // 测试使用错误的签名解密
    public function testDecryptWithInvalidSignature(): void
    {
        $crypt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        // 原始消息
        $originalMsg = '<xml><ToUserName><![CDATA[gh_123456789]]></ToUserName><FromUserName><![CDATA[test_user]]></FromUserName><CreateTime>1234567890</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[test message]]></Content></xml>';
        
        // 加密消息
        $encryptMsg = '';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        
        $encryptCode = $crypt->EncryptMsg($originalMsg, $timestamp, $nonce, $encryptMsg);
        $this->assertEquals(ErrorCode::$OK, $encryptCode);
        
        // 使用错误的签名解密
        $invalidSignature = 'invalid_signature';
        $decryptMsg = '';
        $decryptCode = $crypt->DecryptMsg($invalidSignature, $timestamp, $nonce, $encryptMsg, $decryptMsg);
        
        // 验证解密失败
        $this->assertEquals(ErrorCode::$ValidateSignatureError, $decryptCode);
    }
    
    // 测试使用格式错误的加密消息
    public function testDecryptWithMalformedXml(): void
    {
        $crypt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        
        // 格式错误的XML
        $malformedXml = '<xml><Encrypt>invalid_data</Encrypt></xml>';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        $msgSignature = 'invalid_signature';
        
        $decryptMsg = '';
        $decryptCode = $crypt->DecryptMsg($msgSignature, $timestamp, $nonce, $malformedXml, $decryptMsg);
        
        // 验证解密失败
        $this->assertNotEquals(ErrorCode::$OK, $decryptCode);
    }
} 