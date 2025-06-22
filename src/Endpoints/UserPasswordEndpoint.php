<?php

namespace PinaUsers\Endpoints;

use Pina\App;
use Pina\Composers\CollectionComposer;
use Pina\Data\DataRecord;
use Pina\Http\RichEndpoint;
use Pina\Response;
use PinaUsers\Collections\UserCollection;

use function Pina\__;

class UserPasswordEndpoint extends RichEndpoint
{

    public function title()
    {
        return __('Пароль');
    }

    public function index()
    {

        /** @var CollectionComposer $composer */
        $composer = App::make(CollectionComposer::class);
        $composer->configure($this->title(), '');
        $composer->index($this->location());

        /** @var UserCollection $collection */
        $collection = App::make(UserCollection::class);
        $schema = $collection->getPasswordSchema();

        $form = $this->makeRecordForm($this->location()->link('@'), 'put', new DataRecord([], $schema));
        $form->getButtonRow()->append($this->makeLinkedButton(__('Отменить'), $this->location()->link('@@')));

        return $form->wrap($this->makeSidebarWrapper());
    }


    public function update($tmp, $id)
    {
        $data = $this->request()->all();

        /** @var UserCollection $collection */
        $collection = App::make(UserCollection::class);
        $collection->updatePassword($id, $data);

        return Response::ok()->contentLocation($this->location()->link('@@'));
    }

}