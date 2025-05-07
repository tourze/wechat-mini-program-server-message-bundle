<?php

namespace WechatMiniProgramServerMessageBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;
use WechatMiniProgramServerMessageBundle\Traits\WechatSpuTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/charge/callback/charge_mp_service_validity_notify.html
 */
class WechatSpuDateValidEvent extends UserInteractionEvent
{
    use WechatSpuTrait;

    /**
     * @var string 购买的SPU的有效期到期时间
     */
    private string $validityEndTime;

    public function getValidityEndTime(): string
    {
        return $this->validityEndTime;
    }

    public function setValidityEndTime(string $validityEndTime): void
    {
        $this->validityEndTime = $validityEndTime;
    }
}
