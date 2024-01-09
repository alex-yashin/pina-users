<?php


namespace PinaUsers\Endpoints;


use Pina\Response;
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
        $this->composer->configure(__('Пользователи'), 'Добавить пользователя');
        $this->composer->setItemCallback(
            function (DataRecord $record) {
                $data = $record->getData();
                return $data['first_name'] . ' ' . $data['last_name'];
            }
        );
        $this->collection = $this->export = App::make(UserCollection::class);
    }

    /**
     * @param $id
     * @return \Pina\Controls\Control
     * @throws \Exception
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
     * @throws \Exception
     */
    protected function makePasswordForm()
    {
        $schema = $this->collection->getPasswordSchema();
        return $this->makeEditForm(new DataRecord([], $schema))->setAction($this->location->link('@/password'));
    }


}