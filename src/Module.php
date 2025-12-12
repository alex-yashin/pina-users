<?php

namespace PinaUsers;

use Pina\Access;
use Pina\App;
use Pina\ModuleInterface;
use Pina\Router;
use Pina\Scheduler;
use PinaUsers\Commands\ClearExpiredPasswordRecovery;
use PinaUsers\Endpoints\AuthEndpoint;
use PinaUsers\Endpoints\PasswordRecoveryEndpoint;
use PinaUsers\Endpoints\UserEndpoint;

class Module implements ModuleInterface
{

    public function getPath()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getTitle()
    {
        return 'Users';
    }

    public function __construct()
    {
        App::onLoad(Access::class, function (Access $access) {
            $access->addGroup('public');

            /** @var Auth $auth */
            $auth = App::load(Auth::class);
            if ($auth->userId()) {
                $access->addGroup('registered');
            }
        });

        App::onLoad(Router::class, function (Router $router) {
            $router->register('auth', AuthEndpoint::class)->permit('public');
            $router->register('403', AuthEndpoint::class)->permit('public');
            $router->register('password-recovery', PasswordRecoveryEndpoint::class)->permit('public');

            $router->register('users', UserEndpoint::class)->permit('root');
            $router->register('users/:id/password', Endpoints\UserPasswordEndpoint::class)->permit('root');
        });

        App::onLoad(Scheduler::class, function (Scheduler $scheduler) {
            $scheduler->daily(App::load(ClearExpiredPasswordRecovery::class));
        });
    }
}