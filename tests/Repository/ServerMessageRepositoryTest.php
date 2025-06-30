<?php

declare(strict_types=1);

namespace WechatMiniProgramServerMessageBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramServerMessageBundle\Entity\ServerMessage;
use WechatMiniProgramServerMessageBundle\Repository\ServerMessageRepository;

final class ServerMessageRepositoryTest extends TestCase
{
    private ServerMessageRepository $repository;
    private MockObject&ManagerRegistry $registry;
    private MockObject&EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = ServerMessage::class;
        
        $this->em->method('getClassMetadata')
            ->with(ServerMessage::class)
            ->willReturn($metadata);
        
        $this->registry->method('getManagerForClass')
            ->with(ServerMessage::class)
            ->willReturn($this->em);
        
        $this->repository = new ServerMessageRepository($this->registry);
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(ServerMessageRepository::class, $this->repository);
    }
}