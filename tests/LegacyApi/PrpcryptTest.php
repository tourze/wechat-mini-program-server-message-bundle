<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\Prpcrypt;

final class PrpcryptTest extends TestCase
{
    private Prpcrypt $prpcrypt;
    private string $key = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG';
    private string $appId = 'test-app-id';

    protected function setUp(): void
    {
        $this->prpcrypt = new Prpcrypt($this->key);
    }

    public function testEncryptAndDecrypt(): void
    {
        $text = 'test message';
        
        [$encryptedCode, $encrypted] = $this->prpcrypt->encrypt($text, $this->appId);
        self::assertSame(0, $encryptedCode);
        self::assertNotEmpty($encrypted);

        [$decryptedCode, $decrypted] = $this->prpcrypt->decrypt($encrypted, $this->appId);
        self::assertSame(0, $decryptedCode);
        self::assertSame($text, $decrypted);
    }
}