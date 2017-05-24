<?php
namespace PhalconRoles\Tests\Stubs;

use Phalcon\Mvc\Model;
use PhalconRoles\Contracts\AuthorizationInterface;
use PhalconRoles\Traits\HasRolesAndPermissions;

class Users extends Model implements AuthorizationInterface
{
    use HasRolesAndPermissions;

    public function initialize()
    {
        $this->hasRolesAndPermissions();
    }
}