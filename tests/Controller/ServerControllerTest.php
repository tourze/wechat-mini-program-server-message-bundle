<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Repository\AccountRepository;
use WechatMiniProgramServerMessageBundle\Controller\ServerController;

class ServerControllerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UnitOfWork $unitOfWork;
    private AccountRepository $accountRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;
    private ServerController $controller;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->entityManager->method('getUnitOfWork')->willReturn($this->unitOfWork);
        
        $this->controller = new ServerController($this->entityManager);
    }
    
    // 测试GET请求验证签名成功的情况
    public function testIndexWithGetRequestAndValidSignature(): void
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $token = 'test_token';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        $echostr = 'test_echo_string';
        
        // 计算预期的签名
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $signature = sha1($tmpStr);
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->method('getToken')->willReturn($token);
        $account->method('getAppId')->willReturn($appId);
        
        // 设置仓库返回模拟Account
        $this->accountRepository
            ->method('findOneBy')
            ->with(['appId' => $appId])
            ->willReturn($account);
        
        // 创建请求对象
        $request = Request::create(
            '/wechat/mini-program/server/' . $appId,
            'GET',
            [
                'signature' => $signature,
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'echostr' => $echostr,
            ]
        );
        
        // 执行控制器方法
        $response = $this->controller->__invoke(
            $appId, 
            $request, 
            $this->accountRepository, 
            $this->messageBus, 
            $this->logger
        );
        
        // 验证结果
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($echostr, $response->getContent());
    }
    
    // 测试GET请求验证签名失败的情况
    public function testIndexWithGetRequestAndInvalidSignature(): void
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $token = 'test_token';
        $timestamp = '1234567890';
        $nonce = 'test_nonce';
        $echostr = 'test_echo_string';
        $invalidSignature = 'invalid_signature';
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->method('getToken')->willReturn($token);
        $account->method('getAppId')->willReturn($appId);
        
        // 设置仓库返回模拟Account
        $this->accountRepository
            ->method('findOneBy')
            ->with(['appId' => $appId])
            ->willReturn($account);
        
        // 创建请求对象
        $request = Request::create(
            '/wechat/mini-program/server/' . $appId,
            'GET',
            [
                'signature' => $invalidSignature,
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'echostr' => $echostr,
            ]
        );
        
        // 执行控制器方法
        $response = $this->controller->__invoke(
            $appId, 
            $request, 
            $this->accountRepository, 
            $this->messageBus, 
            $this->logger
        );
        
        // 验证结果
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('error', $response->getContent());
    }
    
    // 测试查找不到appId的情况
    public function testIndexWithNonExistentAppId(): void
    {
        // 准备测试数据
        $appId = 'non_existent_app_id';
        
        // 设置仓库返回null
        $this->accountRepository
            ->method('findOneBy')
            ->with(['appId' => $appId])
            ->willReturn(null);
        
        // 创建请求对象
        $request = Request::create(
            '/wechat/mini-program/server/' . $appId,
            'GET'
        );
        
        // 期望抛出异常
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('找不到小程序');
        
        // 执行控制器方法
        $this->controller->__invoke(
            $appId, 
            $request, 
            $this->accountRepository, 
            $this->messageBus, 
            $this->logger
        );
    }
    
    // 测试POST请求处理JSON消息的情况
    public function testIndexWithPostRequestAndJsonPayload(): void
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $token = 'test_token';
        $appSecret = 'test_app_secret';
        $jsonPayload = '{"ToUserName":"gh_123456789","FromUserName":"test_user","CreateTime":1234567890,"MsgType":"text","Content":"test message"}';
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->method('getToken')->willReturn($token);
        $account->method('getAppSecret')->willReturn($appSecret);
        $account->method('getAppId')->willReturn($appId);
        $account->method('getId')->willReturn(1);
        
        // 设置仓库返回模拟Account
        $this->accountRepository
            ->method('findOneBy')
            ->with(['appId' => $appId])
            ->willReturn($account);
        
        // 消息总线应该接收到消息
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));
        
        // 创建请求对象
        $request = Request::create(
            '/wechat/mini-program/server/' . $appId,
            'POST',
            [],
            [],
            [],
            [],
            $jsonPayload
        );
        
        // 执行控制器方法
        $response = $this->controller->__invoke(
            $appId, 
            $request, 
            $this->accountRepository, 
            $this->messageBus, 
            $this->logger
        );
        
        // 验证结果
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
    
    // 测试POST请求处理XML消息的情况
    public function testIndexWithPostRequestAndXmlPayload(): void
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $token = 'test_token';
        $appSecret = 'test_app_secret';
        $xmlPayload = '<xml><ToUserName><![CDATA[gh_123456789]]></ToUserName><FromUserName><![CDATA[test_user]]></FromUserName><CreateTime>1234567890</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[test message]]></Content></xml>';
        
        // 创建模拟对象
        $account = $this->createMock(Account::class);
        $account->method('getToken')->willReturn($token);
        $account->method('getAppSecret')->willReturn($appSecret);
        $account->method('getAppId')->willReturn($appId);
        $account->method('getId')->willReturn(1);
        
        // 设置仓库返回模拟Account
        $this->accountRepository
            ->method('findOneBy')
            ->with(['appId' => $appId])
            ->willReturn($account);
        
        // 消息总线应该接收到消息
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));
        
        // 创建请求对象
        $request = Request::create(
            '/wechat/mini-program/server/' . $appId,
            'POST',
            [],
            [],
            [],
            [],
            $xmlPayload
        );
        
        // 执行控制器方法
        $response = $this->controller->__invoke(
            $appId, 
            $request, 
            $this->accountRepository, 
            $this->messageBus, 
            $this->logger
        );
        
        // 验证结果
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }
} 