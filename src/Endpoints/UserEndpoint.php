<?php


namespace PinaUsers\Endpoints;


use Exception;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Response;
use PinaUsers\Auth;
use PinaUsers\Collections\UserCollection;
use Pina\App;
use Pina\Data\DataRecord;
use Pina\Http\DelegatedCollectionEndpoint;
use Pina\Http\Request;

use function Pina\__;

class UserEndpoint extends DelegatedCollectionEndpoint
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->composer->configure(__('Пользователи'), __('Добавить пользователя'));
        $this->composer->setItemCallback(
            function (DataRecord $record) {
                $data = $record->getTextData();
                return $data['first_name'] . ' ' . $data['last_name'];
            }
        );
        $this->collection = $this->export = App::make(UserCollection::class);
    }

    /**
     * @param $id
     * @return Control
     * @throws Exception
     */
    public function show($id)
    {
        $r = parent::show($id);
        if ($this->query()->get('display') == 'edit') {
            $r->after($this->makePasswordForm());
        }
        return $r;
    }

    public function updatePassword($tmp, $id)
    {
        $data = $this->request()->all();

        $id = $this->collection->updatePassword($id, $data);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function makePasswordForm()
    {
        $schema = $this->collection->getPasswordSchema();
        return $this->makeEditForm(new DataRecord([], $schema))->setAction($this->location->link('@/password'));
    }

    /**
     * @return ButtonRow
     */
    protected function makeViewButtonRow(DataRecord $record)
    {
        $row = parent::makeViewButtonRow($record);
        $row->append($this->makeActionButton(__('Авторизоваться под пользователем'), $this->location->resource('@/login-as'), 'post'));
        return $row;
    }

    /**
     * @throws Exception
     */
    public function storeLoginAs($tmp, $id)
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        $auth->loginUsingId($id);
        return Response::ok()->contentLocation('/');
    }

}