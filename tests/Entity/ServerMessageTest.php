<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

class ServerMessageTest extends TestCase
{
    // 测试实体初始化后默认值
    public function testInitialState(): void
    {
        $serverMessage = new ServerMessage();

        $this->assertNull($serverMessage->getToUserName());
        $this->assertNull($serverMessage->getFromUserName());
        $this->assertNull($serverMessage->getMsgType());
        $this->assertNull($serverMessage->getMsgId());
        $this->assertNull($serverMessage->getRawData());
        $this->assertNull($serverMessage->getAccount());
        $this->assertEquals(0, $serverMessage->getId());
    }

    // 测试设置和获取ToUserName
    public function testSetAndGetToUserNameWithValidValue(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = 'test_to_user_name';

        $returnValue = $serverMessage->setToUserName($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertEquals($testValue, $serverMessage->getToUserName());
    }

    // 测试设置和获取FromUserName
    public function testSetAndGetFromUserNameWithValidValue(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = 'test_from_user_name';

        $returnValue = $serverMessage->setFromUserName($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertEquals($testValue, $serverMessage->getFromUserName());
    }

    // 测试设置和获取MsgType
    public function testSetAndGetMsgTypeWithValidValue(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = 'text';

        $returnValue = $serverMessage->setMsgType($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertEquals($testValue, $serverMessage->getMsgType());
    }

    // 测试设置和获取MsgId
    public function testSetAndGetMsgIdWithValidValue(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = '123456789';

        $returnValue = $serverMessage->setMsgId($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertEquals($testValue, $serverMessage->getMsgId());
    }

    // 测试设置和获取RawData
    public function testSetAndGetRawDataWithValidArray(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = ['key' => 'value', 'nested' => ['data' => true]];

        $returnValue = $serverMessage->setRawData($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertEquals($testValue, $serverMessage->getRawData());
    }

    // 测试设置和获取RawData，空值情况
    public function testSetAndGetRawDataWithNullValue(): void
    {
        $serverMessage = new ServerMessage();

        $returnValue = $serverMessage->setRawData(null);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertNull($serverMessage->getRawData());
    }

    // 测试设置和获取CreateTime
    public function testSetAndGetCreateTimeWithValidDateTime(): void
    {
        $serverMessage = new ServerMessage();
        $testValue = new \DateTimeImmutable();

        $returnValue = $serverMessage->setCreateTime($testValue);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertSame($testValue, $serverMessage->getCreateTime());
    }

    // 测试设置和获取Account关联
    public function testSetAndGetAccountWithValidAccount(): void
    {
        $serverMessage = new ServerMessage();
        $account = $this->createMock(Account::class);

        $returnValue = $serverMessage->setAccount($account);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertSame($account, $serverMessage->getAccount());
    }

    // 测试设置和获取Account关联，空值情况
    public function testSetAndGetAccountWithNullValue(): void
    {
        $serverMessage = new ServerMessage();

        $returnValue = $serverMessage->setAccount(null);

        $this->assertSame($serverMessage, $returnValue);
        $this->assertNull($serverMessage->getAccount());
    }
}
