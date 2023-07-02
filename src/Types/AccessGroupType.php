<?php


namespace PinaUsers\Types;


use Pina\Types\EnumType;

use function Pina\__;

class AccessGroupType extends EnumType
{
    public function __construct()
    {
        $this->variants = [
            ['id' => 'registered', 'title' => __('Зарегистированный пользователь')],
            ['id' => 'root', 'title' => __('Администратор')],
        ];
    }

    public function getDefault()
    {
        return 'registered';
    }
}