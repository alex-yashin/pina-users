<?php

namespace PinaUsers\Types;

use Pina\Types\StringType;

class PhoneType extends StringType
{
    public function getSize()
    {
        return 32;
    }

}