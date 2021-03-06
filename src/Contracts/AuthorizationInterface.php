<?php

namespace PhalconRoles\Contracts;

use Phalcon\Mvc\Model\ResultInterface;
use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;

interface AuthorizationInterface
{
    /**
     * Return the related roles.
     *
     * @param array $parameters
     * @return Roles[]
     */
    public function getRoles($parameters = null);

    /**
     * Check if the user has a role.
     *
     * @param string $role
     * @return bool
     */
    public function is($role);

    /**
     * Check if the user has role.
     *
     * @param Roles $role
     * @return bool
     */
    public function hasRole(Roles $role);

    /**
     * Attach role to a user.
     *
     * @param Roles $role
     * @return bool
     */
    public function attachRole(Roles $role);

    /**
     * Attach roles to a user.
     *
     * @param Roles[]|ResultInterface $roles
     * @return bool
     */
    public function attachAllRoles($roles);

    /**
     * Detach role from a user.
     *
     * @param Roles $role
     * @return int
     */
    public function detachRole(Roles $role);

    /**
     * Detach all permissions from a user.
     *
     * @return int
     */
    public function detachAllRoles();

    /**
     * Return the related permissions.
     *
     * @return Permissions[]
     */
    public function getPermissions();

    /**
     * Check if the user has a permission.
     *
     * @param string $permission
     * @return bool
     */
    public function can($permission);

    /**
     * Check if user is allowed to perform an action that requires permission,
     *
     * @param $permission
     * @return bool
     */
    public function isAllowed($permission);

    /**
     * Check if the user has a permission.
     *
     * @param Permissions $permission
     * @return bool
     */
    public function hasPermission(Permissions $permission);
}