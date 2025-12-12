<?php

namespace PinaUsers\Types;

use Pina\Types\ValidateException;

use function Pina\__;

class RepeatPasswordType extends PasswordType
{

    protected $password = null;

    public function setContext($context)
    {
        $this->password = $context['password'] ?? null;
        return $this;
    }

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        if ($value != ($this->password ?? '')) {
            throw new ValidateException(__("Пароли не совпадают"));
        }

        return $value;
    }
}