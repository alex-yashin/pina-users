<?php

namespace PinaUsers;

use PinaDashboard\Dashboard;
use Pina\Access;
use Pina\App;
use Pina\ModuleInterface;
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
        App::router()->register('auth', AuthEndpoint::class);
        App::router()->register('password-recovery', PasswordRecoveryEndpoint::class);

        /** @var Dashboard $dashboard */
        $dashboard = App::load(Dashboard::class);
        $dashboard->register('users', UserEndpoint::class);

        Access::addGroup('public');
        Access::permit('auth', 'public');
        Access::permit('password-recovery', 'public');
        Access::permit('users', 'root');

        return [];
    }

}
