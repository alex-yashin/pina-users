<?php

namespace PinaUsers\Endpoints;

use Pina\App;
use Pina\Composers\CollectionComposer;
use Pina\Controls\RecordForm;
use Pina\Data\DataRecord;
use Pina\Http\Request;
use Pina\Http\RichEndpoint;
use Pina\Response;
use PinaUsers\Auth;
use PinaUsers\Collections\UserCollection;

class MyPasswordEndpoint extends RichEndpoint
{

    public function __construct(Request $request)
    {
        parent::__construct($request);

        /** @var CollectionComposer $composer */
        $composer = App::make(CollectionComposer::class);
        $composer->configure('Пароль', '');
        $this->composer = $composer;
    }

    public function title()
    {
        return $this->composer->getCollection();
    }

    /**
     * @return RecordForm
     * @throws \Exception
     */
    public function index()
    {
        $this->composer->index($this->location);

        /** @var UserCollection $collection */
        $collection = App::make(UserCollection::class);
        $record = new DataRecord([], $collection->getPasswordSchema()->setMandatory());

        $view = $this->makeRecordForm($this->location->link('@/password'), 'put', $record);

        return $view->addClass('section')->wrap($this->makeSidebarWrapper());
    }


    /**
     * @return Response
     * @throws \Exception
     */
    public function update($id)
    {
        /** @var UserCollection $collection */
        $collection = App::make(UserCollection::class);

        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        $collection->updatePassword($auth->userId(), $this->request()->all());

        return Response::ok();
    }
}
