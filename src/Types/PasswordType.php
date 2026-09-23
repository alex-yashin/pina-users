<?php

namespace PinaUsers\Types;

use Pina\Controls\Form\FormControl;
use Pina\Controls\Form\FormInput;
use Pina\App;
use Pina\Data\Field;
use Pina\Types\StringType;

class PasswordType extends StringType
{

    public function getSize(): int
    {
        return 60;
    }

    public function makeControl(Field $field, $value): FormControl
    {
        /** @var FormInput $input */
        $input = App::make(FormInput::class);
        $input->setType('password');
        $input->setName($field->getName());
        $input->setTitle($field->getTitle());
        return $input;
    }

}
