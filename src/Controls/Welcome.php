<?php


namespace PinaUsers\Controls;

use Pina\Controls\Card;
use Pina\App;
use Pina\Controls\HandledForm;
use Pina\Controls\SubmitButton;

use function Pina\__;

class Welcome extends Card
{
    public function __construct()
    {
        $this->title = __("Вы авторизованы");
    }

    protected function draw()
    {
        return parent::draw() . $this->makeLogoutForm();
    }

    protected function makeLogoutForm()
    {
        /** @var HandledForm $form */
        $form = App::load(HandledForm::class);
        $form->setAction('auth');
        $form->setMethod('delete');
        $form->append($this->makeLogoutButton());
        return $form;
    }

    protected function makeLogoutButton()
    {
        $button = new SubmitButton();
        $button->setTitle(__("Выйти"));
        return $button;
    }

}