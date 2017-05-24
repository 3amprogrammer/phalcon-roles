<?php

namespace PhalconRoles\Middlewares;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use PhalconRoles\Models\Permissions;

class VerifyPermission extends VerifyAccess
{
    public function __construct($callbackUri)
    {
        parent::__construct($callbackUri);
    }

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $slug = implode('.', [
            $dispatcher->getModuleName(),
            $dispatcher->getControllerName(),
            $dispatcher->getActionName()
        ]);

        $permission = Permissions::findFirstBySlug($slug);

        if(!$permission) {
            return true; // Access to this route is not restricted.
        }

        $user = $this->getIdentity();
        
        if(!$user) {
            $this->response->redirect($this->callbackUri);

            return false;
        }

        if(!$user->hasPermission($permission)) {
            $this->response->redirect($this->callbackUri);

            return false;
        }

        return true;
    }
}