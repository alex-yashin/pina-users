<?php

namespace PinaUsers\Endpoints;

use Pina\App;
use Pina\Controls\RecordForm;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\RichEndpoint;
use Pina\Request;
use PinaNotifications\Messages\Message;
use PinaNotifications\Recipients\EmailRecipient;
use PinaUsers\Controls\AuthWrapper;
use PinaUsers\Hash;
use PinaUsers\PasswordRecoveryGateway;
use PinaUsers\Types\EmailType;

use PinaUsers\Types\PasswordType;
use PinaUsers\Types\RepeatPasswordType;
use PinaUsers\UserGateway;

use Pina\Response;

use function Pina\__;

class PasswordRecoveryEndpoint extends RichEndpoint
{

    /**
     * @return RecordForm
     * @throws \Exception
     */
    public function index()
    {
        Request::setPlace('page_header', __('Восстановить пароль'));

        $form = $this->makeRecordForm($this->location->resource('@'), 'post', new DataRecord([], $this->getEmailSchema()));
        $form->getButtonRow()->getMain()->setTitle(__('Восстановить пароль'));
        $form->getButtonRow()->append($this->makeLinkedButton(__('Вспомнил пароль'), $this->location->link('auth')));

        $status = $this->query()->get('status');
        if ($status == 'success') {
            $form->prepend($this->makeAlert(__('Ссылка отправлена'), 'info'));
        }
        if ($status == 'fail') {
            $form->prepend($this->makeAlert(__('Невозможно отправить ссылку')));
        }

        return $form->wrap(App::make(AuthWrapper::class));
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function store()
    {
        $data = $this->request()->all();
        $normalized = $this->getEmailSchema()->normalize($data);

        $userId = UserGateway::instance()->whereBy("email", $normalized['email'])->id();

        if (empty($userId)) {
            return Response::badRequest(__('Такого пользователя не существует'), 'email');
        }

        $token = PasswordRecoveryGateway::instance()->insertGetId(["user_id" => $userId]);
        $link = $this->location->link('@/:id', ['id' => $token]);

        $success = $this->sendEmail($normalized['email'], $link);

        return Response::ok()->contentLocation($this->location->link('@', ['status' => $success ? 'success' : 'fail']));
    }

    /**
     * @param $id
     * @return RecordForm
     * @throws \Exception
     */
    public function show($id)
    {
        Request::setPlace('page_header', __('Сменить пароль'));

        PasswordRecoveryGateway::instance()->findOrFail($id);

        /** @var RecordForm $form */
        $form = App::load(RecordForm::class);
        $form->setMethod('delete');
        $form->setAction($this->location->resource('@'));
        $form->load(new DataRecord([], $this->getPasswordSchema()));

        return $form->wrap(App::make(AuthWrapper::class));
    }

    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function destroy($id)
    {
        $recovery = PasswordRecoveryGateway::instance()->findOrFail($id);

        $normalized = $this->getPasswordSchema()->normalize($this->request()->all());

        $toUpdate = [];
        $toUpdate['password'] = Hash::make($normalized['password']);

        UserGateway::instance()->whereId($recovery['user_id'])->update($toUpdate);
        PasswordRecoveryGateway::instance()->whereId($id)->delete();

        return Response::ok()->contentLocation($this->location->link('auth', ['message' => 'password_changed']));
    }

    /**
     * @param $email
     * @param $link
     */
    protected function sendEmail($email, $link)
    {
        $recipient = new EmailRecipient($email);
        $message = new Message('Ссылка на восстановление пароля', "Вы или кто-то от вашего имени запросил смену пароля. Если вы не отправляли эту заявку, просто проигнорируйте это письмо.", $link);
        return $recipient->notify($message);
    }

    /**
     * @return Schema
     * @throws \Exception
     */
    protected function getEmailSchema()
    {
        $schema = new Schema();
        $schema->add('email', 'Email', EmailType::class)->setMandatory();
        return $schema;
    }

    /**
     * @return Schema
     * @throws \Exception
     */
    protected function getPasswordSchema()
    {
        $schema = new Schema();
        $schema->add('password', __('Пароль'), PasswordType::class)->setMandatory();
        $schema->add('password2', __('Повторите пароль'), RepeatPasswordType::class)->setMandatory();
        return $schema;
    }


}