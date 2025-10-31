<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatMiniProgramServerMessageBundle\DependencyInjection\WechatMiniProgramServerMessageExtension;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramServerMessageExtension::class)]
final class WechatMiniProgramServerMessageExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
