<?php

namespace WechatMiniProgramServerMessageBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;

final class ServerMessageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $serverMessage = new ServerMessage();
        $serverMessage->setMsgType('text');
        $serverMessage->setToUserName('test_user');
        $serverMessage->setFromUserName('test_from');
        $serverMessage->setMsgId('test_msg_id');
        $serverMessage->setCreateTime(new \DateTimeImmutable());
        $serverMessage->setRawData(['test' => 'data']);

        $manager->persist($serverMessage);
        $manager->flush();
    }
}
