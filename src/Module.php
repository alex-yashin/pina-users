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
        $_SERVER['PINA_USER_ID'] = $userId = App::make(Auth::class)->userId();

        App::router()->register('auth', AuthEndpoint::class);
        App::router()->register('403', AuthEndpoint::class);
        App::router()->register('password-recovery', PasswordRecoveryEndpoint::class);

        /** @var Dashboard $dashboard */
        $dashboard = App::load(Dashboard::class);
        $section = $dashboard->section($this->getTitle());
        $section->register('users', UserEndpoint::class);

        Access::addGroup('public');
        if ($userId) {
            Access::addGroup('registered');
        }
        Access::permit('auth', 'public');
        Access::permit('403', 'public');
        Access::permit('password-recovery', 'public');
        Access::permit('users', 'root');

        return [];
    }

}