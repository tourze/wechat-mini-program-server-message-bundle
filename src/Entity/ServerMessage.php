<?php

namespace WechatMiniProgramServerMessageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository;

#[AsScheduleClean(expression: '17 3 * * *', defaultKeepDay: 180, keepDayEnv: 'WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY')]
#[ORM\Entity(repositoryClass: ServerMessageRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_server_message', options: ['comment' => '服务端消息'])]
class ServerMessage implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createTime = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Account $account = null;

    private ?string $msgId = null;

    private ?string $toUserName = null;

    private ?string $fromUserName = null;

    private ?string $msgType = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始数据'])]
    private ?array $rawData = null;

    public function setCreateTime(?\DateTimeImmutable $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getToUserName(): ?string
    {
        return $this->toUserName;
    }

    public function setToUserName(string $toUserName): self
    {
        $this->toUserName = $toUserName;

        return $this;
    }

    public function getFromUserName(): ?string
    {
        return $this->fromUserName;
    }

    public function setFromUserName(string $fromUserName): self
    {
        $this->fromUserName = $fromUserName;

        return $this;
    }

    public function getMsgType(): ?string
    {
        return $this->msgType;
    }

    public function setMsgType(string $msgType): self
    {
        $this->msgType = $msgType;

        return $this;
    }

    public function getMsgId(): ?string
    {
        return $this->msgId;
    }

    public function setMsgId(string $msgId): self
    {
        $this->msgId = $msgId;

        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
