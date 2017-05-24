<?php

namespace PhalconRoles\Tests\Middlewares;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use PhalconRoles\Contracts\AuthorizationInterface;
use PhalconRoles\Middlewares\VerifyPermission;
use PhalconRoles\Models\Permissions;
use PhalconRoles\Models\Roles;
use PhalconRoles\Tests\Stubs\Users;
use PhalconRoles\Tests\TestCase;
use Phalcon\Http\ResponseInterface;

class VerifyPermissionTest extends TestCase
{
    public function testBeforeExecuteRouteReturnsTrueWhenResourceIsNotProtected()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->never())
            ->method("redirect");

        $dispacher = $this->getDispacherMock();
        $event = $this->createMock(Event::class);

        $middleware = new VerifyPermission("/users/login");

        $middleware->response = $response;

        $this->assertTrue($middleware->beforeExecuteRoute($event, $dispacher));
    }

    public function testBeforeExecuteRouteReturnsFalseAndRedirectsWhenThereIsNoUserRegistered()
    {
        static::$factory->create(Permissions::class, [
            "slug" => "module.controller.action"
        ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method("redirect")
            ->with($this->equalTo("/users/login"));

        $dispacher = $this->getDispacherMock();
        $event = $this->createMock(Event::class);

        $middleware = new VerifyPermission("/users/login");

        $middleware->response = $response;

        $this->assertFalse($middleware->beforeExecuteRoute($event, $dispacher));
    }

    public function testBeforeExecuteRouteReturnsTrueWhenUserHasPermissionAndResourceIsProtected()
    {
        /** @var Roles $role */
        $role = static::$factory->create(Roles::class);

        /** @var Permissions $permission */
        $permission = static::$factory->create(Permissions::class, [
            "slug" => "module.controller.action"
        ]);

        $role->attachPermission($permission);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        $user->attachRole($role);

        VerifyPermission::setIdentity($user);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->never())
            ->method("redirect");

        $dispacher = $this->getDispacherMock();
        $event = $this->createMock(Event::class);

        $middleware = new VerifyPermission("/users/login");

        $middleware->response = $response;

        $this->assertTrue($middleware->beforeExecuteRoute($event, $dispacher));
    }

    public function testBeforeExecuteRouteReturnsFalseAndRedirectsWhenUserHasNoPermissionAndResourceIsProtected()
    {
        /** @var Permissions $permission */
        $permission = static::$factory->create(Permissions::class, [
            "slug" => "module.controller.action"
        ]);

        /** @var AuthorizationInterface $user */
        $user = static::$factory->create(Users::class);

        VerifyPermission::setIdentity($user);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method("redirect")
            ->with($this->equalTo("/users/login"));

        $dispacher = $this->getDispacherMock();
        $event = $this->createMock(Event::class);

        $middleware = new VerifyPermission("/users/login");

        $middleware->response = $response;

        $this->assertFalse($middleware->beforeExecuteRoute($event, $dispacher));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDispacherMock()
    {
        $dispacher = $this->createMock(Dispatcher::class);

        $dispacher->expects($this->any())
            ->method("getModuleName")
            ->will($this->returnValue("module"));

        $dispacher->expects($this->any())
            ->method("getControllerName")
            ->will($this->returnValue("controller"));

        $dispacher->expects($this->any())
            ->method("getActionName")
            ->will($this->returnValue("action"));

        return $dispacher;
    }
}