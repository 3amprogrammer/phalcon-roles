<?php

namespace PhalconRoles\Tests;

use League\FactoryMuffin\FactoryMuffin;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Sqlite as SqliteAdapter;
use Phalcon\Db\AdapterInterface as DbAdapter;
use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\Metadata\Memory as ModelMetadata;
use Phalcon\Test\UnitTestCase as PhalconTestCase;

abstract class TestCase extends PhalconTestCase
{
    /**
     * @var FactoryMuffin
     */
    protected static $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->di->set('config', function () {
            return new Config([
                "database" => [
                    "adapter" => "sqlite",
                    "dbname" => dirname(__DIR__) . '/tests/database.sqlite'
                ]
            ]);
        });

        $this->di->set('db', function () {
            /** @var \Phalcon\Di $this */
            $config = $this->get("config");
            return new SqliteAdapter($config->database->toArray());
        });

        $this->di->set('modelsManager', function () {
            return new ModelManager();
        });

        $this->di->set('modelsMetadata', function () {
            return new ModelMetadata();
        });

        static::$factory = new FactoryMuffin();
        static::$factory->loadFactories(__DIR__ . '/Factories');

        $this->dropTables();
        $this->migrateTables();
    }


    protected function tearDown()
    {
        parent::tearDown();

        $this->dropTables();
    }

    private function migrateTables()
    {
        /** @var DbAdapter $connection */
        $connection = $this->di->get("db");

        $connection->createTable('roles', null, [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'slug',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'slug'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('roles_slug_uindex', ['slug'], 'UNIQUE')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'latin1_swedish_ci'
                ],
            ]
        );

        $connection->createTable('permissions', null, [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'slug',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'slug'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('permissions_slug_uindex', ['slug'], 'UNIQUE')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'latin1_swedish_ci'
                ],
            ]
        );

        $connection->createTable('roles_users', null, [
                'columns' => [
                    new Column(
                        'role_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'user_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'role_id'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['user_id', 'role_id'], 'PRIMARY'),
                    new Index('roles_users_roles_id_fk', ['role_id'], null)
                ],
                'references' => [
                    new Reference(
                        'roles_users_roles_id_fk',
                        [
                            'referencedTable' => 'roles',
                            'columns' => ['role_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'roles_users_users_id_fk',
                        [
                            'referencedTable' => 'users',
                            'columns' => ['user_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'CASCADE'
                        ]
                    )
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'latin1_swedish_ci'
                ],
            ]
        );

        $connection->createTable('roles_permissions', null, [
                'columns' => [
                    new Column(
                        'role_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'permission_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'role_id'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['role_id', 'permission_id'], 'PRIMARY'),
                    new Index('roles_permissions_permission_id_fk', ['permission_id'], null)
                ],
                'references' => [
                    new Reference(
                        'roles_permissions_permission_id_fk',
                        [
                            'referencedTable' => 'permissions',
                            'columns' => ['permission_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'CASCADE'
                        ]
                    ),
                    new Reference(
                        'roles_permissions_roles_id_fk',
                        [
                            'referencedTable' => 'roles',
                            'columns' => ['role_id'],
                            'referencedColumns' => ['id'],
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'CASCADE'
                        ]
                    )
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'latin1_swedish_ci'
                ],
            ]
        );

        $connection->createTable('users', null, [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 128,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'password',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 60,
                            'after' => 'email'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('users_email_uindex', ['email'], 'UNIQUE')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'latin1_swedish_ci'
                ],
            ]
        );
    }

    protected function dropTables()
    {
        /** @var DbAdapter $connection */
        $connection = $this->di->get("db");

        $connection->dropTable('roles_users');
        $connection->dropTable('roles_permissions');
        $connection->dropTable('users');
        $connection->dropTable('roles');
        $connection->dropTable('permissions');
    }
}