<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository;

/**
 * @internal
 */
#[CoversClass(ServerMessageRepository::class)]
#[RunTestsInSeparateProcesses]
final class ServerMessageRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 在这里可以添加额外的设置，如果需要的话
    }

    public function testSaveWithFlushTrue(): void
    {
        $message = new ServerMessage();
        $message->setMsgId('test_msg_001');
        $message->setToUserName('test_to_user');
        $message->setFromUserName('test_from_user');
        $message->setMsgType('text');
        $message->setRawData(['content' => 'test message']);
        $message->setCreateTime(new \DateTimeImmutable());

        $this->getRepository()->save($message, true);

        $savedMessage = $this->getRepository()->find($message->getId());
        $this->assertNotNull($savedMessage);
        $this->assertEquals('test_msg_001', $savedMessage->getMsgId());
        $this->assertEquals('test_to_user', $savedMessage->getToUserName());
        $this->assertEquals('test_from_user', $savedMessage->getFromUserName());
        $this->assertEquals('text', $savedMessage->getMsgType());
        $this->assertEquals(['content' => 'test message'], $savedMessage->getRawData());
    }

    public function testSaveWithFlushFalse(): void
    {
        $message = new ServerMessage();
        $message->setMsgId('test_msg_002');
        $message->setMsgType('event');
        $message->setCreateTime(new \DateTimeImmutable());

        $this->getRepository()->save($message, false);
        self::getEntityManager()->flush();

        $savedMessage = $this->getRepository()->find($message->getId());
        $this->assertNotNull($savedMessage);
        $this->assertEquals('test_msg_002', $savedMessage->getMsgId());
        $this->assertEquals('event', $savedMessage->getMsgType());
    }

    public function testRemoveWithFlushTrue(): void
    {
        $message = new ServerMessage();
        $message->setMsgId('test_msg_003');
        $message->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message, true);
        $id = $message->getId();

        $this->getRepository()->remove($message, true);

        $deletedMessage = $this->getRepository()->find($id);
        $this->assertNull($deletedMessage);
    }

    public function testRemoveWithFlushFalse(): void
    {
        $message = new ServerMessage();
        $message->setMsgId('test_msg_004');
        $message->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message, true);
        $id = $message->getId();

        $this->getRepository()->remove($message, false);
        self::getEntityManager()->flush();

        $deletedMessage = $this->getRepository()->find($id);
        $this->assertNull($deletedMessage);
    }

    public function testFindOneByWithSortingLogic(): void
    {
        $uniqueType = 'sort_test_' . time() . '_' . rand(1000, 9999);

        $message1 = new ServerMessage();
        $message1->setMsgId('sort_msg_001');
        $message1->setMsgType($uniqueType);
        $message1->setCreateTime(new \DateTimeImmutable('2023-01-01'));
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('sort_msg_002');
        $message2->setMsgType($uniqueType);
        $message2->setCreateTime(new \DateTimeImmutable('2023-01-02'));
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['msgType' => $uniqueType], ['createTime' => 'DESC']);
        $this->assertInstanceOf(ServerMessage::class, $result);
        $this->assertEquals('sort_msg_002', $result->getMsgId());
    }

    public function testFindByWithNullValues(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('null_test_001');
        $message1->setToUserName(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('null_test_002');
        $message2->setToUserName('some_user');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['toUserName' => null]);
        $this->assertCount(1, $result);
        $this->assertEquals('null_test_001', $result[0]->getMsgId());
    }

    public function testCountWithNullValues(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('count_null_001');
        $message1->setFromUserName(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('count_null_002');
        $message2->setFromUserName('user123');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['fromUserName' => null]);
        $this->assertEquals(1, $count);
    }

    public function testFindByWithNullAccountValues(): void
    {
        $uniquePrefix = 'null_account_test_' . time() . '_' . rand(1000, 9999);

        $message1 = new ServerMessage();
        $message1->setMsgId($uniquePrefix . '_001');
        $message1->setAccount(null);
        $message1->setMsgType($uniquePrefix . '_type');
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId($uniquePrefix . '_002');
        $message2->setAccount(null);
        $message2->setMsgType($uniquePrefix . '_type');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['account' => null, 'msgType' => $uniquePrefix . '_type']);
        $this->assertCount(2, $result);
        $this->assertEquals($uniquePrefix . '_001', $result[0]->getMsgId());
        $this->assertEquals($uniquePrefix . '_002', $result[1]->getMsgId());
    }

    public function testCountWithNullAccountValues(): void
    {
        $uniquePrefix = 'count_null_account_' . time() . '_' . rand(1000, 9999);

        $message1 = new ServerMessage();
        $message1->setMsgId($uniquePrefix . '_001');
        $message1->setAccount(null);
        $message1->setMsgType($uniquePrefix . '_type');
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId($uniquePrefix . '_002');
        $message2->setAccount(null);
        $message2->setMsgType($uniquePrefix . '_type');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['account' => null, 'msgType' => $uniquePrefix . '_type']);
        $this->assertEquals(2, $count);
    }

    public function testFindOneByWithSortingParameters(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('sort_msg_001');
        $message1->setMsgType('text');
        $message1->setCreateTime(new \DateTimeImmutable('2023-01-01'));
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('sort_msg_002');
        $message2->setMsgType('text');
        $message2->setCreateTime(new \DateTimeImmutable('2023-01-02'));
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['msgType' => 'text'], ['id' => 'DESC']);
        $this->assertInstanceOf(ServerMessage::class, $result);
        $this->assertEquals('sort_msg_002', $result->getMsgId());
    }

    public function testFindByWithAccountAssociation(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');
        $account->setName('Test Account Name');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);
        self::getEntityManager()->persist($account);

        $message = new ServerMessage();
        $message->setMsgId('account_msg_001');
        $message->setAccount($account);
        $message->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['account' => $account]);
        $this->assertCount(1, $result);
        $this->assertEquals('account_msg_001', $result[0]->getMsgId());
    }

    public function testCountWithAccountAssociation(): void
    {
        $account = new Account();
        $account->setAppId('test_app_count');
        $account->setName('Test Account Count');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);
        self::getEntityManager()->persist($account);

        $message = new ServerMessage();
        $message->setMsgId('count_account_msg');
        $message->setAccount($account);
        $message->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['account' => $account]);
        $this->assertEquals(1, $count);
    }

    public function testFindByWithMsgIdIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_msg_id');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['msgId' => null]);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]->getMsgId());
    }

    public function testFindByWithMsgTypeIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('null_type_test');
        $message1->setMsgType(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_type_test');
        $message2->setMsgType('text');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['msgType' => null]);
        $this->assertCount(1, $result);
        $this->assertEquals('null_type_test', $result[0]->getMsgId());
    }

    public function testCountWithMsgIdIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_msg_id_count');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['msgId' => null]);
        $this->assertEquals(1, $count);
    }

    public function testCountWithMsgTypeIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('null_type_count');
        $message1->setMsgType(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_type_count');
        $message2->setMsgType('event');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['msgType' => null]);
        $this->assertEquals(1, $count);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $uniqueType = 'order_by_test_' . time() . '_' . rand(1000, 9999);

        $message1 = new ServerMessage();
        $message1->setMsgId('first_message');
        $message1->setMsgType($uniqueType);
        $message1->setCreateTime(new \DateTimeImmutable('2023-01-01'));
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('second_message');
        $message2->setMsgType($uniqueType);
        $message2->setCreateTime(new \DateTimeImmutable('2023-01-02'));
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['msgType' => $uniqueType], ['createTime' => 'ASC']);
        $this->assertNotNull($result);
        $this->assertEquals('first_message', $result->getMsgId());

        $result = $this->getRepository()->findOneBy(['msgType' => $uniqueType], ['createTime' => 'DESC']);
        $this->assertNotNull($result);
        $this->assertEquals('second_message', $result->getMsgId());
    }

    public function testFindByWithAccountIsNull(): void
    {
        $uniqueId = 'no_account_test_' . time() . '_' . rand(1000, 9999);

        $message1 = new ServerMessage();
        $message1->setMsgId($uniqueId);
        $message1->setAccount(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['msgId' => $uniqueId]);
        $this->assertCount(1, $result);
        $this->assertEquals($uniqueId, $result[0]->getMsgId());
    }

    public function testCountWithAccountIsNull(): void
    {
        $initialCount = $this->getRepository()->count(['account' => null]);

        $message1 = new ServerMessage();
        $message1->setMsgId('no_account_count');
        $message1->setAccount(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['account' => null]);
        $this->assertEquals($initialCount + 1, $count);
    }

    public function testFindByWithCreateTimeIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('no_create_time');
        $message1->setCreateTime(null);
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_create_time');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['createTime' => null]);
        $this->assertCount(1, $result);
        $this->assertEquals('no_create_time', $result[0]->getMsgId());
    }

    public function testCountWithCreateTimeIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('no_create_time_count');
        $message1->setCreateTime(null);
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_create_time_count');
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['createTime' => null]);
        $this->assertEquals(1, $count);
    }

    public function testFindByWithRawDataIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('no_raw_data');
        $message1->setRawData(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_raw_data');
        $message2->setRawData(['key' => 'value']);
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findBy(['rawData' => null]);
        $this->assertCount(1, $result);
        $this->assertEquals('no_raw_data', $result[0]->getMsgId());
    }

    public function testCountWithRawDataIsNull(): void
    {
        $message1 = new ServerMessage();
        $message1->setMsgId('no_raw_data_count');
        $message1->setRawData(null);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('has_raw_data_count');
        $message2->setRawData(['key' => 'value']);
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['rawData' => null]);
        $this->assertEquals(1, $count);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setAppId('test_association_app');
        $account->setName('Test Association App');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);
        self::getEntityManager()->persist($account);

        $message = new ServerMessage();
        $message->setMsgId('association_test_msg');
        $message->setAccount($account);
        $message->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message, false);

        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['account' => $account]);
        $this->assertInstanceOf(ServerMessage::class, $result);
        $this->assertEquals('association_test_msg', $result->getMsgId());
        $this->assertEquals($account, $result->getAccount());
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $account = new Account();
        $account->setAppId('test_count_association_app');
        $account->setName('Test Count Association App');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);
        self::getEntityManager()->persist($account);

        $message1 = new ServerMessage();
        $message1->setMsgId('count_association_msg_1');
        $message1->setAccount($account);
        $message1->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message1, false);

        $message2 = new ServerMessage();
        $message2->setMsgId('count_association_msg_2');
        $message2->setAccount($account);
        $message2->setCreateTime(new \DateTimeImmutable());
        $this->getRepository()->save($message2, false);

        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['account' => $account]);
        $this->assertEquals(2, $count);
    }

    protected function createNewEntity(): object
    {
        $entity = new ServerMessage();
        $entity->setMsgId('test_msg_' . uniqid());
        $entity->setToUserName('test_to_user_' . uniqid());
        $entity->setFromUserName('test_from_user_' . uniqid());
        $entity->setMsgType('text');
        $entity->setRawData(['content' => 'test message ' . uniqid()]);
        $entity->setCreateTime(new \DateTimeImmutable());

        return $entity;
    }

    /**
     * @return ServerMessageRepository
     */
    protected function getRepository(): ServerMessageRepository
    {
        return self::getService(ServerMessageRepository::class);
    }
}
