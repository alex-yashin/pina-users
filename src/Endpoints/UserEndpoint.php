<?php


namespace PinaUsers\Endpoints;


use PinaUsers\Collections\UserCollection;
use Pina\App;
use Pina\Data\DataRecord;
use Pina\Http\DelegatedCollectionEndpoint;
use Pina\Http\Request;

class UserEndpoint extends DelegatedCollectionEndpoint
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->composer->configure('Users', 'Create user');
        $this->composer->setItemCallback(
            function (DataRecord $record) {
                $data = $record->getData();
                return $data['firstname'] . ' ' . $data['lastname'];
            }
        );
        $this->collection = $this->export = App::make(UserCollection::class);
    }

}