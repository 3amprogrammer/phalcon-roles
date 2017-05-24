<?php

namespace PhalconRoles\Tests\Models;

use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;
use PhalconRoles\Models\RolesPermissions;
use PhalconRoles\Tests\TestCase;

class RolesTest extends TestCase
{
    public function testAttachPermission()
    {
        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        // call to getPermissions caches permissions
        $role->getPermissions();

        $role->attachPermission($permissionA);
        $role->attachPermission($permissionB);

        // result in db ok
        $resultSet = RolesPermissions::find(["role_id", $role->id])->toArray();
        $this->assertEquals(array_column($resultSet, 'permission_id'), [$permissionA->id, $permissionB->id]);

        // result in model ok
        $this->assertTrue($role->hasPermission($permissionA));
        $this->assertTrue($role->hasPermission($permissionB));
    }

    public function testAttachAllPermissions()
    {
        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        // call to getPermissions caches permissions
        $role->getPermissions();

        $role->attachAllPermissions([$permissionA, $permissionB]);

        // result in db ok
        $resultSet = RolesPermissions::find(["role_id", $role->id])->toArray();
        $this->assertEquals(array_column($resultSet, 'permission_id'), [$permissionA->id, $permissionB->id]);

        // result in model ok
        $this->assertTrue($role->hasPermission($permissionA));
        $this->assertTrue($role->hasPermission($permissionB));
    }

    public function testAttachAllPermissionsDoesntInsertDuplicates()
    {
        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        $role->attachPermission($permissionA);
        $role->attachAllPermissions([$permissionA, $permissionB]);

        // result in db ok
        $resultSet = RolesPermissions::find(["role_id", $role->id])->toArray();
        $this->assertEquals(array_column($resultSet, 'permission_id'), [$permissionA->id, $permissionB->id]);
    }

    public function testDetachPermission()
    {
        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        $role->attachPermission($permissionA);
        $role->attachPermission($permissionB);

        // call to getPermissions caches permissions
        $role->getPermissions();

        $role->detachPermission($permissionA);

        // result in db is ok
        $resultSet = RolesPermissions::find(["role_id", $role->id])->toArray();
        $this->assertEquals(array_column($resultSet, 'permission_id'), [$permissionB->id]);

        // result in model is ok
        $this->assertTrue($role->hasPermission($permissionB));
        $this->assertFalse($role->hasPermission($permissionA));
    }

    public function testDetachAllPermissions()
    {
        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        $role->attachPermission($permissionA);
        $role->attachPermission($permissionB);

        // call to getPermissions caches permissions
        $role->getPermissions();

        $role->detachAllPermissions();

        // result in db is ok
        $resultSet = RolesPermissions::find(["role_id", $role->id])->toArray();
        $this->assertEquals(array_column($resultSet, 'permission_id'), []);

        // result in model is ok
        $this->assertFalse($role->hasPermission($permissionA));
        $this->assertFalse($role->hasPermission($permissionB));
    }
}
