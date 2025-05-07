<?php

namespace WechatMiniProgramServerMessageBundle\Message;

use Tourze\Symfony\Async\Message\AsyncMessageInterface;

class ServerPayloadReceivedMessage implements AsyncMessageInterface
{
    /**
     * @var array 消息内容
     */
    private array $payload;

    private string $accountId;

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): void
    {
        $this->accountId = $accountId;
    }
}
