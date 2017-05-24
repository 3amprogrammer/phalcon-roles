<?php

namespace PhalconRoles\Traits;

use Closure;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;
use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;
use PhalconRoles\Models\RolesPermissions;
use PhalconRoles\Models\RolesUsers;

trait HasRolesAndPermissions
{
    /**
     * Always use getter/setter, event inside the class.
     * See coresponding methods for further informations.
     *
     * @var Resultset
     */
    protected $roles;

    /**
     * Always use getter/setter, event inside the class.
     * See coresponding methods for further informations.
     *
     * @var Resultset
     */
    protected $permissions;

    protected abstract function hasMany(
        $fields,
        $referenceModel,
        $referencedFields,
        $options = null
    );

    protected abstract function hasManyToMany(
        $fields,
        $intermediateModel,
        $intermediateFields,
        $intermediateReferencedFields,
        $referenceModel,
        $referencedFields,
        $options = null
    );

    protected abstract function getRelated($alias, $arguments = null);

    public function hasRolesAndPermissions()
    {
        $this->hasMany(
            "id",
            RolesUsers::class,
            "user_id",
            ['alias' => 'rolesPivot']
        );

        $this->hasManyToMany(
            "id",
            RolesUsers::class,
            "user_id",
            "role_id",
            Roles::class,
            "id",
            ['alias' => 'roles']
        );
    }

    /**
     * Return the related roles
     *
     * @param array $parameters
     * @return Roles[]
     */
    public function getRoles($parameters = null)
    {
        if ($this->roles === null) {
            $this->roles = $this->toHydratedArrary(
                $this->getRelated("roles", $parameters), Roles::class
            );
        }

        return $this->roles;
    }

    /**
     * Check if the user has a role
     *
     * @param string $role
     * @return bool
     */
    public function is($role)
    {
        return $this->hasRole(new Roles(["slug" => $role]));
    }

    /**
     * Check if the user has role.
     *
     * @param Roles $role
     * @return bool
     */
    public function hasRole(Roles $role)
    {
        $byName = function (Roles $record) use ($role) {
            return $record->slug === $role->slug ? $record : false;
        };

        return (bool)array_filter($this->getRoles(), $byName);
    }

    /**
     * Attach role to a user.
     *
     * @param Roles $role
     * @return bool
     */
    public function attachRole(Roles $role)
    {
        if ($this->hasRole($role)) {
            return true;
        }

        $this->setRoles([$role]);

        return $this->save();
    }

    /**
     * Attach roles to a user.
     *
     * @param array $roles
     * @return bool
     */
    public function attachAllRoles(array $roles)
    {
        $rolesToAttach = [];

        foreach ($roles as $role) {
            if(!$this->hasRole($role)) {
                $rolesToAttach[] = $role;
            }
        }

        $this->setRoles($roles);

        return $this->save();
    }

    /**
     * Detach role from a user.
     *
     * @param Roles $role
     * @return int
     */
    public function detachRole(Roles $role)
    {
        $byName = function (RolesUsers $record) use ($role) {
            return $record->role_id === $role->id;
        };

        return $this->_detachRoles($byName);
    }

    /**
     * Detach all permissions from a user.
     *
     * @return int
     */
    public function detachAllRoles()
    {
        return $this->_detachRoles();
    }

    protected function _detachRoles(Closure $filter = null) {
        if(!$this->rolesPivot->delete($filter)) {
            return false;
        }

        $this->roles = null;

        return true;
    }

    /**
     * @return Permissions[]
     */
    public function getPermissions()
    {
        if ($this->permissions === null) {
            $roles = $this->getRoles();
            $roleIDs = array_column($roles, 'id');

            $builder = $this->getModelsManager()->createBuilder();
            $builder->columns('DISTINCT p.id, p.name, p.slug, p.description');
            $builder->from(['rp' => RolesPermissions::class]);
            $builder->join(Permissions::class, 'rp.permission_id = p.id', 'p');
            $builder->inWhere('rp.role_id', $roleIDs);

            $resultSet = $builder->getQuery()->execute();

            $this->permissions = $this->toHydratedArrary($resultSet, Permissions::class);
        }

        return $this->permissions;
    }

    /**
     * Check if the user has a permission.
     *
     * @param string $permission
     * @return bool
     */
    public function can($permission)
    {
        return $this->hasPermission(new Permissions(["slug" => $permission]));
    }

    public function hasPermission(Permissions $permission)
    {
        $byName = function (Permissions $record) use ($permission) {
            return $record->slug === $permission->slug ? $record : false;
        };

        return (bool)array_filter($this->getPermissions(), $byName);
    }

    private function setRoles(array $roles)
    {
        // getRoles caches the result set inside roles property.
        // Magic __set doesn't set this property, so to avoid inconsistent
        // state between model and database after persisting new roles,
        // we need to set roles to null, to force refetching on next access.
        $this->roles = null;

        // We need to explicitly call magic __set to prepare object
        // for persisting relations on save by seting _relation property.
        $this->__set("roles", $roles);
    }

    private function toHydratedArrary(ResultsetInterface $resultset, $modelClass)
    {
        return array_map(
            function($row) use ($modelClass) {
                return new $modelClass($row);
            },
            $resultset->toArray()
        );
    }
}