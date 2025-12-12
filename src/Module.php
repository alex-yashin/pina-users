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
        Access::addGroup('public');

        App::onLoad(Router::class, function (Router $router) {
            /** @var Auth $auth */
            $auth = App::load(Auth::class);
            $_SERVER['PINA_USER_ID'] = $userId = $auth->userId();
            if ($userId) {
                Access::addGroup('registered');
            }

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