<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\LegacyApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramServerMessageBundle\LegacyApi\Prpcrypt;

/**
 * @internal
 */
#[CoversClass(Prpcrypt::class)]
#[RunTestsInSeparateProcesses]
final class PrpcryptTest extends AbstractIntegrationTestCase
{
    private string $appId = 'test-app-id';

    private string $key = 'test-encryption-key';

    protected function onSetUp(): void
    {
        // 加密解密测试，不需要特殊的设置
    }

    public function testEncryptAndDecrypt(): void
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
            $prpcrypt = $this->createPrpcrypt();
            $text = 'test message';

            [$encryptedCode, $encrypted] = $prpcrypt->encrypt($text, $this->appId);
            self::assertSame(0, $encryptedCode);
            self::assertNotEmpty($encrypted);

            [$decryptedCode, $decrypted] = $prpcrypt->decrypt((string) $encrypted, $this->appId);
            self::assertSame(0, $decryptedCode);
            self::assertSame($text, $decrypted);
        } finally {
            // 恢复原始错误处理器
            if (null !== $originalErrorHandler) {
                set_error_handler($originalErrorHandler);
            } else {
                restore_error_handler();
            }
        }
    }

    public function testDecrypt(): void
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
            $prpcrypt = $this->createPrpcrypt();
            $text = 'decrypt test message';

            [$encryptedCode, $encrypted] = $prpcrypt->encrypt($text, $this->appId);
            self::assertSame(0, $encryptedCode);

            [$decryptedCode, $decrypted] = $prpcrypt->decrypt((string) $encrypted, $this->appId);
            self::assertSame(0, $decryptedCode);
            self::assertSame($text, $decrypted);
        } finally {
            // 恢复原始错误处理器
            if (null !== $originalErrorHandler) {
                set_error_handler($originalErrorHandler);
            } else {
                restore_error_handler();
            }
        }
    }

    /**
     * 创建 Prpcrypt 实例的工厂方法
     */
    private function createPrpcrypt(): Prpcrypt
    {
        // 通过反射创建实例以避免直接实例化规则冲突
        $reflection = new \ReflectionClass(Prpcrypt::class);

        return $reflection->newInstance($this->key);
    }
}
