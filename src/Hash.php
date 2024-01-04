<?php

namespace PinaUsers;

use Pina\App;

class Hash
{

    public static function make($password)
    {
        /** @var Model\Hash $impl */
        $impl = App::load(Model\Hash::class);
        return $impl->make($password);
    }

    public static function check($password, $hash)
    {
        /** @var Model\Hash $impl */
        $impl = App::load(Model\Hash::class);
        return $impl->check($password, $hash);
    }

}