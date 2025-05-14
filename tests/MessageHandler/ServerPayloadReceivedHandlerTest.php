<?php

namespace WechatMiniProgramServerMessageBundle\Tests\MessageHandler;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use WechatMiniProgramServerMessageBundle\Message\ServerPayloadReceivedMessage;
use WechatMiniProgramServerMessageBundle\MessageHandler\ServerPayloadReceivedHandler;

class ServerPayloadReceivedHandlerTest extends TestCase
{
    private UserRepository $userRepository;
    private UserLoaderInterface $userLoader;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;
    private AccountRepository $accountRepository;
    private CacheInterface $cache;
    private EntityManagerInterface $entityManager;
    private ServerPayloadReceivedHandler $handler;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->handler = new ServerPayloadReceivedHandler(
            $this->userRepository,
            $this->userLoader,
            $this->eventDispatcher,
            $this->logger,
            $this->accountRepository,
            $this->cache,
            $this->entityManager
        );
    }
    
    // 测试空消息负载的情况
    public function testInvokeWithEmptyPayload(): void
    {
        // 创建消息
        $message = new ServerPayloadReceivedMessage();
        $message->setPayload([]);
        $message->setAccountId('1');
        
        // 期望抛出异常
        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('回调数据Payload不能为空');
        
        // 执行处理器
        $this->handler->__invoke($message);
    }
    
    // 测试找不到账号的情况
    public function testInvokeWithNonExistentAccount(): void
    {
        // 创建消息
        $message = new ServerPayloadReceivedMessage();
        $message->setPayload(['CreateTime' => 1234567890]);
        $message->setAccountId('999');
        
        // 设置账号仓库返回null
        $this->accountRepository
            ->method('find')
            ->with('999')
            ->willReturn(null);
        
        // 期望抛出异常
        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('找不到小程序账号');
        
        // 执行处理器
        $this->handler->__invoke($message);
    }
    
    // 测试缓存中已存在消息的情况
    public function testInvokeWithCachedMessage(): void
    {
        // 创建消息
        $message = new ServerPayloadReceivedMessage();
        $message->setPayload([
            'CreateTime' => 1234567890,
            'MsgId' => 'test_msg_id',
            'MsgType' => 'text',
            'ToUserName' => 'gh_123456789',
            'FromUserName' => 'test_user',
        ]);
        $message->setAccountId('1');
        
        // 创建模拟账号
        $account = $this->createMock(Account::class);
        
        // 设置账号仓库返回模拟账号
        $this->accountRepository
            ->method('find')
            ->with('1')
            ->willReturn($account);
        
        // 设置缓存已存在该消息
        $cacheKey = ServerMessageRequestEvent::class . 'test_msg_id';
        $this->cache
            ->method('has')
            ->with($cacheKey)
            ->willReturn(true);
        
        // 日志应该记录缓存存在的信息
        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('缓存存在了，即为处理过了'),
                $this->callback(function ($context) use ($cacheKey) {
                    return isset($context['cacheKey']) && $context['cacheKey'] === $cacheKey;
                })
            );
        
        // 执行处理器 - 应该提前返回，不会处理消息
        $this->handler->__invoke($message);
    }
    
    // 测试正常处理流程
    public function testInvokeWithValidMessageAndNewUser(): void
    {
        // 创建消息
        $message = new ServerPayloadReceivedMessage();
        $message->setPayload([
            'CreateTime' => 1234567890,
            'MsgId' => 'test_msg_id',
            'MsgType' => 'text',
            'ToUserName' => 'gh_123456789',
            'FromUserName' => 'test_user',
        ]);
        $message->setAccountId('1');
        
        // 创建模拟账号
        $account = $this->createMock(Account::class);
        
        // 设置账号仓库返回模拟账号
        $this->accountRepository
            ->method('find')
            ->with('1')
            ->willReturn($account);
        
        // 设置缓存不存在该消息
        $cacheKey = ServerMessageRequestEvent::class . 'test_msg_id';
        $this->cache
            ->method('has')
            ->with($cacheKey)
            ->willReturn(false);
        
        // 设置用户加载器返回null（表示新用户）
        $this->userLoader
            ->method('loadUserByOpenId')
            ->with('test_user')
            ->willReturn(null);
        
        // entityManager应该persist两次（一次是ServerMessage，一次是User）
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;
                
                if ($callCount === 1) {
                    $this->assertInstanceOf(ServerMessage::class, $entity);
                } elseif ($callCount === 2) {
                    $this->assertInstanceOf(User::class, $entity);
                }
                
                return null;
            });
        
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');
        
        // userRepository应该尝试转换用户
        $this->userRepository
            ->expects($this->once())
            ->method('transformToSysUser')
            ->with($this->isInstanceOf(User::class));
        
        // 事件分发器应该分发事件
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ServerMessageRequestEvent::class))
            ->willReturnSelf();
        
        // 缓存应该设置
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($cacheKey),
                $this->isType('integer'),
                $this->equalTo(60 * 60)
            );
        
        // 执行处理器
        $this->handler->__invoke($message);
    }
    
    // 测试唯一约束异常的情况
    public function testInvokeWithUniqueConstraintViolation(): void
    {
        // 创建消息
        $message = new ServerPayloadReceivedMessage();
        $message->setPayload([
            'CreateTime' => 1234567890,
            'MsgId' => 'test_msg_id',
            'MsgType' => 'text',
            'ToUserName' => 'gh_123456789',
            'FromUserName' => 'test_user',
        ]);
        $message->setAccountId('1');
        
        // 创建模拟账号
        $account = $this->createMock(Account::class);
        
        // 设置账号仓库返回模拟账号
        $this->accountRepository
            ->method('find')
            ->with('1')
            ->willReturn($account);
        
        // 设置缓存不存在该消息
        $cacheKey = ServerMessageRequestEvent::class . 'test_msg_id';
        $this->cache
            ->method('has')
            ->with($cacheKey)
            ->willReturn(false);
        
        // entityManager应该抛出唯一约束异常
        $exception = $this->createMock(UniqueConstraintViolationException::class);
        $this->entityManager
            ->method('persist')
            ->with($this->isInstanceOf(ServerMessage::class));
        $this->entityManager
            ->method('flush')
            ->willThrowException($exception);
        
        // 日志应该记录警告
        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                $this->equalTo('服务端通知重复，不处理'),
                $this->callback(function ($context) use ($exception) {
                    return isset($context['message']) && isset($context['exception'])
                        && $context['exception'] === $exception;
                })
            );
        
        // 执行处理器 - 应该捕获异常并提前返回
        $this->handler->__invoke($message);
    }
} 