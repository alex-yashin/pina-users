<?php


namespace PinaUsers\Types;


use Pina\Types\EnumType;

class AccessGroupType extends EnumType
{
    public function __construct()
    {
        $this->variants = [
            ['id' => 'registered', 'title' => 'Registered user'],
            ['id' => 'root', 'title' => 'Administrator'],
        ];
    }

    public function getDefault()
    {
        return 'registered';
    }
}