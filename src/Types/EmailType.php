<?php

namespace PinaUsers\Types;

use Pina\Types\StringType;
use Pina\Types\ValidateException;

use function Pina\__;

class EmailType extends StringType
{
    public function getSize(): int
    {
        return 256;
    }

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidateException(__("Укажите корректный email"));
        }

        return $value;
    }

}