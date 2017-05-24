<?php

namespace PhalconRoles\Tests\Traits;

use PhalconRoles\Contracts\AuthorizationInterface;
use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;
use PhalconRoles\Models\RolesUsers;
use PhalconRoles\Tests\TestCase;
use PhalconRoles\Tests\Stubs\Users;

class HasRolesAndPermissionsTest extends TestCase
{
    public function testAttachPermission()
    {
        /** @var Roles $roleA */
        /** @var Roles $roleB */
        $roleA = static::$factory->create(Roles::class);
        $roleB = static::$factory->create(Roles::class);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        // call to getRoles caches permissions
        $user->getRoles();

        $user->attachRole($roleA);
        $user->attachRole($roleB);

        // result in db ok
        $resultSet = RolesUsers::find(["user_id", $user->id])->toArray();
        $this->assertEquals(array_column($resultSet, "role_id"), [$roleA->id, $roleB->id]);

        // result in model ok
        $this->assertTrue($user->hasRole($roleA));
        $this->assertTrue($user->hasRole($roleB));
    }

    public function testAttachAllRoles()
    {
        /** @var Roles $roleA */
        /** @var Roles $roleB */
        $roleA = static::$factory->create(Roles::class);
        $roleB = static::$factory->create(Roles::class);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        // call to getRoles caches permissions
        $user->getRoles();

        $user->attachAllRoles([$roleA, $roleB]);

        // result in db ok
        $resultSet = RolesUsers::find(["user_id", $user->id])->toArray();
        $this->assertEquals(array_column($resultSet, "role_id"), [$roleA->id, $roleB->id]);

        // result in model ok
        $this->assertTrue($user->hasRole($roleA));
        $this->assertTrue($user->hasRole($roleB));
    }

    public function testAttachAllRolesDoesntInsertDuplicates()
    {
        /** @var Roles $roleA */
        /** @var Roles $roleB */
        $roleA = static::$factory->create(Roles::class);
        $roleB = static::$factory->create(Roles::class);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        $user->attachRole($roleA);
        $user->attachAllRoles([$roleA, $roleB]);

        // result in db ok
        $resultSet = RolesUsers::find(["user_id", $user->id])->toArray();
        $this->assertEquals(array_column($resultSet, "role_id"), [$roleA->id, $roleB->id]);
    }

    public function testDetachRole()
    {
        /** @var Roles $roleA */
        /** @var Roles $roleB */
        $roleA = static::$factory->create(Roles::class);
        $roleB = static::$factory->create(Roles::class);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        $user->attachRole($roleA);
        $user->attachRole($roleB);

        // call to getRoles caches permissions
        $user->getRoles();

        $user->detachRole($roleA);

        // result in db is ok
        $resultSet = RolesUsers::find(["user_id", $user->id])->toArray();
        $this->assertEquals(array_column($resultSet, "role_id"), [$roleB->id]);

        // result in model is ok
        $this->assertTrue($user->hasRole($roleB));
        $this->assertFalse($user->hasRole($roleA));
    }

    public function testDetachAllRoles()
    {
        /** @var Roles $roleA */
        /** @var Roles $roleB */
        $roleA = static::$factory->create(Roles::class);
        $roleB = static::$factory->create(Roles::class);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        $user->attachRole($roleA);
        $user->attachRole($roleB);

        // call to getRoles caches permissions
        $user->getRoles();

        $user->detachAllRoles();

        // result in db is ok
        $resultSet = RolesUsers::find(["user_id", $user->id])->toArray();
        $this->assertEquals(array_column($resultSet, "role_id"), []);

        // result in model is ok
        $this->assertFalse($user->hasRole($roleA));
        $this->assertFalse($user->hasRole($roleB));
    }

    public function testUserIs()
    {
        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        /** @var Users $role */
        $user = static::$factory->create(Users::class);
        $user->attachRole($role);

        $this->assertTrue($user->is($role->slug));

        $this->assertFalse($user->is("admin"));
    }

    public function testUserCan()
    {
        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        /** @var Permissions $permissionA */
        /** @var Permissions $permissionB */
        $permissionA = static::$factory->create(Permissions::class);
        $permissionB = static::$factory->create(Permissions::class);

        $role->attachPermission($permissionA);
        $role->attachPermission($permissionB);

        /** @var Users $role */
        $user = static::$factory->create(Users::class);
        $user->attachRole($role);

        $this->assertTrue($user->can($permissionA->slug));
        $this->assertTrue($user->can($permissionB->slug));

        $this->assertFalse($user->can("perform.some.action"));
    }
}
