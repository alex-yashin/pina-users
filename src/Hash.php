<?php

namespace PinaUsers;

use Pina\App;

class Hash
{

    public static function make($password)
    {
        return Model\Hash::load()->make($password);
    }

    public static function check($password, $hash)
    {
        return Model\Hash::load()->check($password, $hash);
    }

}