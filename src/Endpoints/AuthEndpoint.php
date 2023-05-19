<?php

namespace PinaUsers\Endpoints;

use Pina\App;
use Pina\Controls\LinkedButton;
use Pina\Controls\RecordForm;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\Endpoint;
use PinaUsers\Auth;
use PinaUsers\Controls\Welcome;
use PinaUsers\Types\PasswordType;

use Pina\Response;
use Pina\Types\StringType;

use function Pina\__;

class AuthEndpoint extends Endpoint
{

    /**
     * @return Welcome|RecordForm
     * @throws \Exception
     */
    public function index()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        if ($auth->isSignedIn()) {
            /** @var Welcome $welcome */
            $welcome = App::load(Welcome::class);
            return $welcome;
        }

        /** @var RecordForm $form */
        $form = App::load(RecordForm::class);
        $form->setMethod('post');
        $form->load(new DataRecord([], $this->getSchema()));

        /** @var LinkedButton $authButton */
        $authButton = App::make(LinkedButton::class);
        $authButton->setLink($this->location->link('password-recovery'));
        $authButton->setTitle(__('Восстановить пароль'));
        $form->getButtonRow()->append($authButton);

        return $form;
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
}