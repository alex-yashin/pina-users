<?php

namespace PinaUsers\Model;

use Pina\App;

class Hash
{
    public static function load(): Hash
    {
        return App::load(static::class);
    }

    public function make($password)
    {
        $salt = substr(str_replace('+', '.', base64_encode(sha1(microtime(true), true))), 0, 22);
        return crypt($password, '$2a$12$' . $salt);
    }

    public function check($password, $hash)
    {
        return $hash == crypt($password, $hash);
    }
}