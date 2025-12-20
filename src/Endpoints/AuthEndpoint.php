<?php

namespace PinaUsers\Endpoints;

use Exception;
use Pina\App;
use Pina\Controls\ButtonRow;
use Pina\Controls\HandledForm;
use Pina\Controls\Nav\Nav;
use Pina\Controls\SubmitButton;
use Pina\Controls\Wrapper;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\RichEndpoint;
use PinaUsers\Auth;
use PinaUsers\Layouts\DialogLayout;
use PinaUsers\Types\PasswordType;

use Pina\Response;
use Pina\Types\StringType;

use function Pina\__;

class AuthEndpoint extends RichEndpoint
{

    /**
     * @throws Exception
     */
    public function index()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        if ($auth->isSignedIn()) {
            App::place('page_header')->set(__('Добро пожаловать'));

            $form = $this->makeHandledForm($this->location()->link('auth'), 'delete');
            $form->addClass('form-logout');
            $form->append($this->makeDashboardMenu());
            $form->append($this->makeLogoutButton());
            return $form->setLayout(App::make(DialogLayout::class));
        }

        App::place('page_header')->set(__('Войти'));

        $form = $this->makeRecordForm($this->location()->link('@'), 'post', new DataRecord([], $this->getSchema()));
        $form->getButtonRow()->getMain()->setTitle(__('Войти'));
        $form->getButtonRow()->append($this->makeLinkedButton(__('Восстановить пароль'), $this->location()->link('password-recovery')));

        return $form->setLayout(App::make(DialogLayout::class));
    }

    protected function makeHandledForm($action, $method): HandledForm
    {
        /** @var HandledForm $form */
        $form = App::load(HandledForm::class);
        $form->setAction($action);
        $form->setMethod($method);
        return $form;
    }

    /**
     * @throws Exception
     */
    public function store()
    {
        $data = $this->request()->all();
        $normalized = $this->getSchema()->normalize($data);

        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        if (!$auth->attempt($normalized['email'], $normalized['password'])) {
            return Response::badRequest(__("Неверный логин или пароль"), "password");
        }
        return Response::ok();
    }

    /**
     * @throws Exception
     */
    public function destroy()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        $auth->logout();
        return Response::ok();
    }

    /**
     * @throws Exception
     */
    protected function getSchema(): Schema
    {
        $schema = new Schema();
        $schema->add('email', 'Email', StringType::class)->setMandatory();
        $schema->add('password', __('Пароль'), PasswordType::class)->setMandatory();
        return $schema;
    }

    protected function makeDashboardMenu(): Nav
    {
        /** @var Nav $nav */
        $nav = App::make(Nav::class);
        $menu = App::router()->getMenu();
        foreach ($menu as $linkedItem) {
            $nav->appendLink($linkedItem->getTitle(), $linkedItem->getLink());
        }
        $nav->wrap(new Wrapper('nav.card'));
        return $nav;
    }

    protected function makeLogoutButton()
    {
        $button = new SubmitButton();
        $button->setTitle(__("Выйти"));

        $row = App::make(ButtonRow::class);
        $row->append($button);
        return $row;
    }
}