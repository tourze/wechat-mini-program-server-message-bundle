<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramServerMessageBundle\Exception\UserTransformException;

/**
 * @internal
 */
#[CoversClass(UserTransformException::class)]
final class UserTransformExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(UserTransformException::class);
        $this->expectExceptionMessage('Test message');

        throw new UserTransformException('Test message');
    }

    public function testExceptionExtendsException(): void
    {
        $exception = new UserTransformException('Test');

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
