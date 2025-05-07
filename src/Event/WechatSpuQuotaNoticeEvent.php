<?php

namespace WechatMiniProgramServerMessageBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;
use WechatMiniProgramServerMessageBundle\Traits\WechatSpuTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/charge/callback/charge_mp_service_quota_notify.html
 */
class WechatSpuQuotaNoticeEvent extends UserInteractionEvent
{
    use WechatSpuTrait;

    private int $totalQuota;

    private int $totalUsedQuota;

    public function getTotalQuota(): int
    {
        return $this->totalQuota;
    }

    public function setTotalQuota(int $totalQuota): void
    {
        $this->totalQuota = $totalQuota;
    }

    public function getTotalUsedQuota(): int
    {
        return $this->totalUsedQuota;
    }

    public function setTotalUsedQuota(int $totalUsedQuota): void
    {
        $this->totalUsedQuota = $totalUsedQuota;
    }
}
