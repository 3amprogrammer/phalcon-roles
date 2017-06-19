<?php

namespace PhalconRoles\Models;

use Closure;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\ResultsetInterface;

class Roles extends Model
{
    public $id;

    public $name;

    public $slug;

    public $description;

    protected $permissions = [];

    public function initialize()
    {
        $this->hasMany(
            "id",
            RolesPermissions::class,
            "role_id",
            ['alias' => 'permissionsPivot']
        );

        $this->hasManyToMany(
            "id",
            RolesPermissions::class,
            "role_id",
            "permission_id",
            Permissions::class,
            "id",
            ["alias" => "permissions"]
        );
    }

    /**
     * @param array $parameters
     * @return Permissions[]
     */
    public function getPermissions($parameters = null)
    {
        if (empty($this->permissions)) {
            $this->permissions = $this->toHydratedArrary(
                $this->getRelated("permissions", $parameters), Permissions::class
            );
        }

        return $this->permissions;
    }

    /**
     * Check if the user has role.
     *
     * @param Permissions $permission
     * @return bool
     */
    public function hasPermission(Permissions $permission)
    {
        $byName = function (Permissions $record) use ($permission) {
            return $record->slug === $permission->slug ? $record : false;
        };

        return (bool)array_filter($this->getPermissions(), $byName);
    }

    /**
     * Attach permission to a user.
     *
     * @param Permissions $permission
     * @return bool
     */
    public function attachPermission(Permissions $permission)
    {
        if ($this->hasPermission($permission)) {
            return true;
        }

        $this->setPermissions([$permission]);

        return $this->save();
    }

    /**
     * Attach permissions to a user.
     *
     * @param Permissions[]|ResultInterface $permissions
     * @return bool
     */
    public function attachAllPermissions($permissions)
    {
        $permissionsToAttach = [];

        foreach ($permissions as $permission) {
            if(!$this->hasPermission($permission)) {
                $permissionsToAttach[] = $permission;
            }
        }

        $this->setPermissions($permissionsToAttach);

        return $this->save();
    }

    /**
     * Detach permission from a user.
     *
     * @param Permissions $permission
     * @return int
     */
    public function detachPermission(Permissions $permission)
    {
        $byName = function (RolesPermissions $record) use ($permission) {
            return $record->permission_id === $permission->id;
        };

        return $this->_detachPermissions($byName);
    }

    /**
     * Detach all permissions from a user.
     *
     * @param Closure|null $filter
     * @return int
     */
    public function detachAllPermissions(Closure $filter = null)
    {
        return $this->_detachPermissions($filter);
    }

    protected function _detachPermissions(Closure $filter = null) {
        if(!$this->permissionsPivot->delete($filter)) {
           return false;
        }

        $this->permissions = null;

        return true;
    }

    protected function setPermissions(array $permissions)
    {
        // getPermissions caches the result set inside permissions property.
        // Magic __set doesn't set this property, so to avoid inconsistent
        // state between model and database after persisting new permissions,
        // we need to set permissions to null, to force refetching on next access.
        $this->permissions = null;

        // We need to explicitly call magic __set to prepare object
        // for persisting relations on save by seting _relation property.
        $this->__set("permissions", $permissions);
    }

    protected function toHydratedArrary(ResultsetInterface $resultset, $modelClass)
    {
        return array_map(
            function ($row) use ($modelClass) {
                return new $modelClass($row);
            },
            $resultset->toArray()
        );
    }
}
