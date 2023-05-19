<?php


namespace PinaUsers\Types;


use Pina\Types\IntegerType;

class UnsignedIntegerType extends IntegerType
{
    public function getSQLType()
    {
        return "int(" . $this->getSize() . ") unsigned ";
    }
}