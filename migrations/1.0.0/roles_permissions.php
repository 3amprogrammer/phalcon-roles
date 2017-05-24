<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class RolesPermissionsMigration_100
 */
class RolesPermissionsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('roles_permissions', [
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
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        $this->morph();
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
