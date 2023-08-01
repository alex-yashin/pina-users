<?php

namespace PinaUsers\Types;

use Pina\Html;
use Pina\Types\StringType;
use Pina\Types\ValidateException;

use function Pina\__;

class PhoneType extends StringType
{
    public function getSize(): int
    {
        return 64;
    }

    public function draw($value): string
    {
        return Html::a(parent::draw($value), 'tel:+' . $value);
    }

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        $value = preg_replace('/[^\d]/', '', $value ?? '');
        if ((empty($value) || strlen($value) <= 3) && $isMandatory) {
            throw new ValidateException(__("Укажите телефон"));
        }
        return $value;
    }

}