<?php

namespace PinaUsers\Types;

use Pina\Types\ValidateException;

use function Pina\__;

class RepeatPasswordType extends PasswordType
{

    protected $context = [];

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        if ($value != ($this->context['password'] ?? '')) {
            throw new ValidateException(__("Пароли не совпадают"));
        }

        return $value;
    }
}