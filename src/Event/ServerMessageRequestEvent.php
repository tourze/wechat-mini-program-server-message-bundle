<?php

namespace WechatMiniProgramServerMessageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\Entity\Account;

class ServerMessageRequestEvent extends Event
{
    /**
     * @var array 发送的消息
     */
    private array $message;

    /**
     * @var Account 发送账号
     */
    private Account $account;

    private User $wechatUser;

    public function getMessage(): array
    {
        return $this->message;
    }

    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getWechatUser(): User
    {
        return $this->wechatUser;
    }

    public function setWechatUser(User $wechatUser): void
    {
        $this->wechatUser = $wechatUser;
    }
}
