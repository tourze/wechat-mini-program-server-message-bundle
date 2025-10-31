<?php

namespace WechatMiniProgramServerMessageBundle\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Persistence\ObjectManager;

/**
 * 创建测试环境所需但包中不包含的表
 *
 * 这个 Fixture 用于解决测试框架尝试清理不存在的表导致的错误
 */
class CreateBizUserBizRoleTableFixture extends Fixture implements OrderedFixtureInterface
{
    public function getOrder(): int
    {
        return -1000; // 确保在其他 fixtures 之前执行
    }

    public function load(ObjectManager $manager): void
    {
        $connection = $manager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        // 检查 biz_user 表是否存在
        if (!$schemaManager->tablesExist(['biz_user'])) {
            $table = new Table('biz_user');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('email', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('password', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('username', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('roles', 'json', ['notnull' => false]);
            $table->setPrimaryKey(['id'], 'pk_biz_user');

            $schemaManager->createTable($table);
        }

        // 检查 biz_role 表是否存在
        if (!$schemaManager->tablesExist(['biz_role'])) {
            $table = new Table('biz_role');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('code', 'string', ['length' => 255, 'notnull' => false]);
            $table->setPrimaryKey(['id'], 'pk_biz_role');

            $schemaManager->createTable($table);
        }

        // 检查 biz_user_biz_role 表是否存在
        if (!$schemaManager->tablesExist(['biz_user_biz_role'])) {
            $table = new Table('biz_user_biz_role');
            $table->addColumn('biz_user_id', 'integer');
            $table->addColumn('biz_role_id', 'integer');
            $table->setPrimaryKey(['biz_user_id', 'biz_role_id'], 'pk_biz_user_biz_role');

            $schemaManager->createTable($table);
        }
    }
}
