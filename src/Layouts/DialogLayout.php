<?php

namespace PinaUsers\Layouts;

use Pina\Html;
use Pina\Layouts\DefaultLayout;

class DialogLayout extends DefaultLayout
{

    protected function drawBody()
    {
        return $this->drawHeader()
            . $this->drawPageHeader()
            . $this->drawDialog()
            . $this->drawFooter();
    }

    protected function drawDialog()
    {
        return Html::nest('.container', $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter());
    }
}