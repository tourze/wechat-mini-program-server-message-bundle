<?php

namespace WechatMiniProgramServerMessageBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository;

#[AsScheduleClean(expression: '17 3 * * *', defaultKeepDay: 180, keepDayEnv: 'WECHAT_MINI_PROGRAM_SERVER_MESSAGE_PERSIST_DAY')]
#[ORM\Entity(repositoryClass: ServerMessageRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_server_message', options: ['comment' => '服务端消息'])]
class ServerMessage implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createTime = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Account $account = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '消息ID'])]
    private ?string $msgId = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '接收方用户名'])]
    private ?string $toUserName = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '发送方用户名'])]
    private ?string $fromUserName = null;

    #[Assert\Length(max: 50)]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '消息类型'])]
    private ?string $msgType = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始数据'])]
    private ?array $rawData = null;

    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getToUserName(): ?string
    {
        return $this->toUserName;
    }

    public function setToUserName(?string $toUserName): void
    {
        $this->toUserName = $toUserName;
    }

    public function getFromUserName(): ?string
    {
        return $this->fromUserName;
    }

    public function setFromUserName(?string $fromUserName): void
    {
        $this->fromUserName = $fromUserName;
    }

    public function getMsgType(): ?string
    {
        return $this->msgType;
    }

    public function setMsgType(?string $msgType): void
    {
        $this->msgType = $msgType;
    }

    public function getMsgId(): ?string
    {
        return $this->msgId;
    }

    public function setMsgId(?string $msgId): void
    {
        $this->msgId = $msgId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * @param array<string, mixed>|null $rawData
     */
    public function setRawData(?array $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
