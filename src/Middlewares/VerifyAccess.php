<?php

namespace PhalconRoles\Middlewares;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
use PhalconRoles\Contracts\AuthorizationInterface;

abstract class VerifyAccess extends Plugin
{
    protected $callbackUri;

    protected static $identity;

    public static function setIdentity($identity)
    {
        self::$identity = $identity;
    }

    public function __construct($callbackUri)
    {
        $this->callbackUri = $callbackUri;
    }

    public abstract function beforeExecuteRoute(Event $event, Dispatcher $dispatcher);

    /**
     * @return AuthorizationInterface
     */
    public function getIdentity()
    {
        if(self::$identity !== null) {
            return self::$identity;
        }

        if($this->di->has("user")) {
            return $this->di->get("user");
        }

        return null;
    }
}