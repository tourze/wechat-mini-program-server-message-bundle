<?php

namespace WechatMiniProgramServerMessageBundle\Message;

use Tourze\AsyncContracts\AsyncMessageInterface;

class ServerPayloadReceivedMessage implements AsyncMessageInterface
{
    /**
     * @var array<string, mixed> 消息内容
     */
    private array $payload;

    private string $accountId;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
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
