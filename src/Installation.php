<?php

namespace PinaUsers;

use Pina\InstallationInterface;

class Installation implements InstallationInterface
{

    public function prepare()
    {
    }

    public function install()
    {
        self::createAdminUser();
    }
    
    public function createAdminUser()
    {
        $userExists = UserGateway::instance()->exists();
        if (empty($userExists)) {
            UserGateway::instance()->insert(array(
                'email' => 'admin',
                'password' => Hash::make('admin'),
                'group' => 'root',
            ));
        }
    }

    public function remove()
    {
    }

}
