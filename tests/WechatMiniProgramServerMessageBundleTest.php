<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatMiniProgramServerMessageBundle\WechatMiniProgramServerMessageBundle;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramServerMessageBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramServerMessageBundleTest extends AbstractBundleTestCase
{
}
