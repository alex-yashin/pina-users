<?php


namespace PinaUsers\Controls;


use Pina\Controls\Control;
use Pina\Html;

class AuthWrapper extends Control
{

    protected function draw()
    {
        return Html::nest(
            '.limited container section',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter()
        );
    }

}