<?php

namespace PinaUsers\Endpoints;

use Pina\App;
use Pina\Controls\ButtonRow;
use Pina\Controls\HandledForm;
use Pina\Controls\LinkedButton;
use Pina\Controls\RecordForm;
use Pina\Controls\SubmitButton;
use Pina\Controls\Wrapper;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\Endpoint;
use Pina\Request;
use PinaDashboard\Widgets\Menu;
use PinaUsers\Auth;
use PinaUsers\Controls\AuthWrapper;
use PinaUsers\Types\PasswordType;

use Pina\Response;
use Pina\Types\StringType;

use function Pina\__;

class AuthEndpoint extends Endpoint
{

    /**
     * @throws \Exception
     */
    public function index()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        if ($auth->isSignedIn()) {
            Request::setPlace('page_header', __('Добро пожаловать'));

            /** @var HandledForm $form */
            $form = App::load(HandledForm::class);
            $form->addClass('form-logout');
            $form->setAction('auth');
            $form->setMethod('delete');
            $form->append($this->makeDashboardMenu());
            $form->append($this->makeLogoutButton());
            return $form->wrap(App::make(AuthWrapper::class));
        }

        Request::setPlace('page_header', __('Войти'));

        /** @var RecordForm $form */
        $form = App::load(RecordForm::class);
        $form->setMethod('post');
        $form->setAction($this->location->link('@'));
        $form->load(new DataRecord([], $this->getSchema()));

        $form->getButtonRow()->getMain()->setTitle(__('Войти'));

        /** @var LinkedButton $authButton */
        $authButton = App::make(LinkedButton::class);
        $authButton->setLink($this->location->link('password-recovery'));
        $authButton->setTitle(__('Восстановить пароль'));
        $form->getButtonRow()->append($authButton);

        return $form->wrap(App::make(AuthWrapper::class));
    }

    /**
     * @return Response
     * @throws \Exception
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
     * @return Response
     * @throws \Exception
     */
    public function destroy()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        $auth->logout();
        return Response::ok();
    }

    /**
     * @return Schema
     * @throws \Exception
     */
    protected function getSchema()
    {
        $schema = new Schema();
        $schema->add('email', 'Email', StringType::class)->setMandatory();
        $schema->add('password', __('Пароль'), PasswordType::class)->setMandatory();
        return $schema;
    }

    protected function makeDashboardMenu()
    {
        /** @var Menu $menu */
        $menu = App::make(Menu::class);
        $menu->wrap(new Wrapper('nav.card'));
        return $menu;
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