<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramServerMessageBundle\Controller\ServerController;

/**
 * @internal
 */
#[CoversClass(ServerController::class)]
#[RunTestsInSeparateProcesses]
final class ServerControllerTest extends AbstractWebTestCase
{
    public function testControllerEndpoint(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // Create a test account for the appId parameter
        $account = new Account();
        $account->setName('Test Mini Program');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        // Test GET request with proper signature validation
        $timestamp = (string) time();
        $nonce = 'test_nonce';
        $echostr = 'test_echo';

        // Generate valid signature based on controller logic
        $tmpArr = ['test_token', $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $signature = sha1(implode($tmpArr));

        $client->request('GET', '/wechat/mini-program/server/test_app_id', [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'echostr' => $echostr,
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame($echostr, $client->getResponse()->getContent());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();

        // Create a test account for the appId parameter
        $account = new Account();
        $account->setName('Test Mini Program');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');
        $account->setToken('test_token');
        $account->setValid(true);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($account);
        $entityManager->flush();

        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/wechat/mini-program/server/test_app_id');
    }
}
