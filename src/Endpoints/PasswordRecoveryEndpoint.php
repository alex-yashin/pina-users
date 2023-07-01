<?php

namespace PinaUsers\Endpoints;

use PHPMailer\PHPMailer\PHPMailer;
use Pina\App;
use Pina\Controls\LinkedButton;
use Pina\Controls\RecordForm;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\Endpoint;
use Pina\Request;
use PinaUsers\Controls\AuthWrapper;
use PinaUsers\Hash;
use PinaUsers\PasswordRecoveryGateway;
use PinaUsers\Types\EmailType;

use PinaUsers\Types\PasswordType;
use PinaUsers\Types\RepeatPasswordType;
use PinaUsers\UserGateway;

use Pina\Response;

use function Pina\__;

class PasswordRecoveryEndpoint extends Endpoint
{

    /**
     * @return RecordForm
     * @throws \Exception
     */
    public function index()
    {

        Request::setPlace('page_header', __('Восстановить пароль'));

        /** @var RecordForm $form */
        $form = App::load(RecordForm::class);
        $form->setAction($this->location->resource('@'));
        $form->setMethod('post');
        $form->load(new DataRecord([], $this->getEmailSchema()));

        /** @var LinkedButton $authButton */
        $authButton = App::make(LinkedButton::class);
        $authButton->setLink($this->location->link('auth'));
        $authButton->setTitle(__('Вспомнил пароль'));
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
        $normalized = $this->getEmailSchema()->normalize($data);

        $userId = UserGateway::instance()->whereBy("email", $normalized['email'])->id();

        if (empty($userId)) {
            return Response::badRequest(__('Такого пользователя не существует'), 'email');
        }

        $token = PasswordRecoveryGateway::instance()->insertGetId(["user_id" => $userId]);
        $link = $this->location->link('@/:id', ['id' => $token]);

        $this->sendEmail($normalized['email'], $link);

        return Response::ok();
    }

    /**
     * @param $id
     * @return RecordForm
     * @throws \Exception
     */
    public function show($id)
    {
        PasswordRecoveryGateway::instance()->findOrFail($id);

        /** @var RecordForm $form */
        $form = App::load(RecordForm::class);
        $form->setMethod('delete');
        $form->setAction($this->location->resource('@'));
        $form->load(new DataRecord([], $this->getPasswordSchema()));

        return $form;
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
     * @throws \PHPMailer\PHPMailer\Exception
     */
    protected function sendEmail($email, $link)
    {
        //TODO отвязаться от PHPMailer`а
        /** @var PHPMailer $mailer */
        $mailer = App::load(PHPMailer::class);
        $mailer->Subject = 'Ссылка на восстановление пароля';
        $mailer->Body = "Здравствуйте!\nВы или кто-то от вашего имени запросил смену пароля. Если вы не отправляли эту заявку, просто проигнорируйте это письмо.\nСсылка для восстановления пароля:\n$link";
        $mailer->addAddress($email);
        $mailer->send();
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