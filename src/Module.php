<?php

namespace PinaUsers;

use Pina\Access;
use Pina\App;
use Pina\ModuleInterface;
use PinaUsers\Commands\ClearExpiredPasswordRecovery;
use PinaUsers\Endpoints\AuthEndpoint;
use PinaUsers\Endpoints\PasswordRecoveryEndpoint;
use PinaUsers\Endpoints\UserEndpoint;

class Module implements ModuleInterface
{

    public function __construct()
    {
    }

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

    public function http()
    {
        Access::addGroup('public');

        $_SERVER['PINA_USER_ID'] = $userId = App::load(Auth::class)->userId();
        if ($userId) {
            Access::addGroup('registered');
        }

        App::router()->register('auth', AuthEndpoint::class);
        Access::permit('auth', 'public');

        App::router()->register('403', AuthEndpoint::class);
        Access::permit('403', 'public');

        App::router()->register('password-recovery', PasswordRecoveryEndpoint::class);
        Access::permit('password-recovery', 'public');

        App::router()->register('users', UserEndpoint::class);
        Access::permit('users', 'root');

        return [];
    }

    /**
     * @param \Pina\Scheduler $scheduler
     */
    public function schedule($scheduler)
    {
        $scheduler->daily(App::load(ClearExpiredPasswordRecovery::class));
    }

}