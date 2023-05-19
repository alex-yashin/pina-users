<?php

namespace PinaUsers\Types;

use Pina\Types\StringType;
use Pina\Types\ValidateException;

use function Pina\__;

class UsernameType extends StringType
{

    public function getSize()
    {
        return 16;
    }

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        $value = mb_strtolower($value);
        if (!preg_match("/^[a-z\d_]{2,16}$/i", $value)) {
            throw new ValidateException(__("Поддерживаются символы латиницы, цифры и знак подчеркивания в сумме от 2 до 16 символов"));
        }

        return $value;
    }


}