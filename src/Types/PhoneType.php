<?php

namespace PinaUsers\Types;

use Pina\Types\StringType;

class PhoneType extends StringType
{
    public function getSize(): int
    {
        return 32;
    }

}