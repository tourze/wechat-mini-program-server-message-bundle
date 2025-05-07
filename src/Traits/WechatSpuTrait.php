<?php

namespace WechatMiniProgramServerMessageBundle\Traits;

use WechatMiniProgramBundle\Entity\Account;

trait WechatSpuTrait
{
    /**
     * @var string 购买的商品的SPU_ID
     */
    private string $spuId;

    /**
     * @var string 购买的商品的SPU_NAME
     */
    private string $spuName;

    private Account $account;

    public function getSpuId(): string
    {
        return $this->spuId;
    }

    public function setSpuId(string $spuId): void
    {
        $this->spuId = $spuId;
    }

    public function getSpuName(): string
    {
        return $this->spuName;
    }

    public function setSpuName(string $spuName): void
    {
        $this->spuName = $spuName;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }
}
