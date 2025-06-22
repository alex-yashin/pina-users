<?php


namespace PinaUsers\Endpoints;


use Exception;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Data\DataCollection;
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
    protected function getCollectionTitle(): string
    {
        return __('Пользователи');
    }

    protected function makeDataCollection(): DataCollection
    {
        return App::make(UserCollection::class);
    }

    protected function makeExportDataCollection(): ?DataCollection
    {
        return $this->makeDataCollection();
    }

    /**
     * @return ButtonRow
     */
    protected function makeViewButtonRow(DataRecord $record): ButtonRow
    {
        $row = parent::makeViewButtonRow($record);
        $row->append($this->makeActionButton(__('Авторизоваться под пользователем'), $this->location()->resource('@/login-as'), 'post'));
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