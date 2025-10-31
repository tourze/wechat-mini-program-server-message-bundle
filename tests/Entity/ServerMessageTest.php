<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

/**
 * @internal
 */
#[CoversClass(ServerMessage::class)]
final class ServerMessageTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ServerMessage();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'toUserName' => ['toUserName', 'test_user'],
            'fromUserName' => ['fromUserName', 'from_user'],
            'msgType' => ['msgType', 'text'],
            'msgId' => ['msgId', 'msg_123'],
            'rawData' => ['rawData', ['key' => 'value']],
        ];
    }

    public function testGetId(): void
    {
        $serverMessage = new ServerMessage();
        $this->assertSame(0, $serverMessage->getId());
    }

    public function testSetAndGetCreateTime(): void
    {
        $serverMessage = new ServerMessage();
        $createTime = new \DateTimeImmutable();
        $serverMessage->setCreateTime($createTime);
        $this->assertSame($createTime, $serverMessage->getCreateTime());
    }

    public function testSetAndGetToUserName(): void
    {
        $serverMessage = new ServerMessage();
        $toUserName = 'test_to_user';
        $serverMessage->setToUserName($toUserName);
        $this->assertSame($toUserName, $serverMessage->getToUserName());
    }

    public function testSetAndGetFromUserName(): void
    {
        $serverMessage = new ServerMessage();
        $fromUserName = 'test_from_user';
        $serverMessage->setFromUserName($fromUserName);
        $this->assertSame($fromUserName, $serverMessage->getFromUserName());
    }

    public function testSetAndGetMsgType(): void
    {
        $serverMessage = new ServerMessage();
        $msgType = 'text';
        $serverMessage->setMsgType($msgType);
        $this->assertSame($msgType, $serverMessage->getMsgType());
    }

    public function testSetAndGetMsgId(): void
    {
        $serverMessage = new ServerMessage();
        $msgId = 'msg_123456';
        $serverMessage->setMsgId($msgId);
        $this->assertSame($msgId, $serverMessage->getMsgId());
    }

    public function testSetAndGetRawData(): void
    {
        $serverMessage = new ServerMessage();
        $rawData = ['key' => 'value', 'data' => 'test'];
        $serverMessage->setRawData($rawData);
        $this->assertSame($rawData, $serverMessage->getRawData());

        $serverMessage->setRawData(null);
        $this->assertNull($serverMessage->getRawData());
    }

    public function testSetAndGetAccount(): void
    {
        $serverMessage = new ServerMessage();
        // 使用具体类Account的mock是必要的，因为：
        // 1. Account是实体类，没有对应的接口
        // 2. 测试需要验证实体之间的关联关系
        // 3. Doctrine实体通常直接依赖具体类而非接口
        $account = $this->createMock(Account::class);
        $serverMessage->setAccount($account);
        $this->assertSame($account, $serverMessage->getAccount());

        $serverMessage->setAccount(null);
        $this->assertNull($serverMessage->getAccount());
    }

    public function testToString(): void
    {
        $serverMessage = new ServerMessage();
        $this->assertSame('0', (string) $serverMessage);
    }

    public function testStringableInterface(): void
    {
        $serverMessage = new ServerMessage();
        $this->assertInstanceOf(\Stringable::class, $serverMessage);
    }

    public function testInitialNullValues(): void
    {
        $serverMessage = new ServerMessage();
        $this->assertNull($serverMessage->getCreateTime());
        $this->assertNull($serverMessage->getToUserName());
        $this->assertNull($serverMessage->getFromUserName());
        $this->assertNull($serverMessage->getMsgType());
        $this->assertNull($serverMessage->getMsgId());
        $this->assertNull($serverMessage->getRawData());
        $this->assertNull($serverMessage->getAccount());
    }
}
