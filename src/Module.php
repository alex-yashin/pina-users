<?php

namespace PinaUsers;

use Pina\Access;
use Pina\App;
use Pina\ModuleInterface;
use Pina\Scheduler;
use PinaUsers\Commands\ClearExpiredPasswordRecovery;
use PinaUsers\Router\MyProfileRouter;
use PinaUsers\Router\UserAdminRouter;
use PinaUsers\Router\AuthRouter;

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

        App::onLoad(AuthRouter::class, function (AuthRouter $router) {
            $router->register('auth', Endpoints\AuthEndpoint::class)->permit('public');
            $router->register('password-recovery', Endpoints\PasswordRecoveryEndpoint::class)->permit('public');
        });

        App::onLoad(MyProfileRouter::class, function (MyProfileRouter $router) {
            $router->register('my-profile', Endpoints\MyProfileEndpoint::class)->permit('registered');
            $router->register('my-password', Endpoints\MyPasswordEndpoint::class)->permit('registered');
        });

        App::onLoad(UserAdminRouter::class, function (UserAdminRouter $router) {
            $router->register('users', Endpoints\UserEndpoint::class)->permit('root');
            $router->register('users/:user_id/password', Endpoints\UserPasswordEndpoint::class)->permit('root');
        });

        App::onLoad(Scheduler::class, function (Scheduler $scheduler) {
            $scheduler->daily(App::load(ClearExpiredPasswordRecovery::class));
        });
    }
}