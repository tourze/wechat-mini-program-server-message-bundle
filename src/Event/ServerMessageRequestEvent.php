<?php

namespace WechatMiniProgramServerMessageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Entity\Account;

final class ServerMessageRequestEvent extends Event
{
    /**
     * @var array<string, mixed> 发送的消息
     */
    private array $message;

    /**
     * @var Account 发送账号
     */
    private Account $account;

    private UserInterface $wechatUser;

    /**
     * @return array<string, mixed>
     */
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * @param array<string, mixed> $message
     */
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

    public function getWechatUser(): UserInterface
    {
        return $this->wechatUser;
    }

    public function setWechatUser(UserInterface $wechatUser): void
    {
        $this->wechatUser = $wechatUser;
    }
}
