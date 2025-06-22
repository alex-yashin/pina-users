<?php


namespace PinaUsers\Endpoints;


use Pina\App;
use Pina\Composers\CollectionComposer;
use Pina\Controls\RecordForm;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\ForbiddenException;
use Pina\Http\RichEndpoint;
use Pina\Response;
use PinaUsers\Auth;
use PinaUsers\SQL\UserGateway;

class MyProfileEndpoint extends RichEndpoint
{

    public function title()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);
        return UserGateway::instance()->whereBy('id', $auth->userId())->selectTitle()->value('title');
    }

    /**
     * @return RecordForm
     * @throws \Exception
     */
    public function index()
    {
        /** @var CollectionComposer $composer */
        $composer = App::make(CollectionComposer::class);
        $composer->configure($this->title(), '');
        $composer->index($this->location());

        $query = $this->makeQuery();
        $query->selectId();

        $schema = $this->getSchema();

        foreach ($schema as $field) {
            $fieldName = $field->getName();
            if ($query->hasField($fieldName)) {
                $query->select($fieldName);
            }
        }

        $line = $query->first();
        $schema->fill($line);

        $schema->forgetField('id');

        $record = new DataRecord($line, $schema);
        $view = $this->makeRecordForm($this->location()->link('@'), 'put', $record);

        return $view->addClass('section')->wrap($this->makeSidebarWrapper());

    }

    protected function getSchema(): Schema
    {
        $schema = $this->makeQuery()->getSchema()->forgetStatic();
        foreach ($schema as $field) {
            if (!$field->hasTag('my-profile')) {
                $schema->forgetField($field->getName());
            }
        }
        return $schema;
    }

    /**
     * @return UserGateway
     * @throws \Exception
     */
    protected function makeQuery()
    {
        /** @var Auth $auth */
        $auth = App::load(Auth::class);

        if (empty($auth->userId())) {
            throw new ForbiddenException();
        }

        return UserGateway::instance()
            ->whereId($auth->userId());
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function update()
    {
        $data = $this->request()->all();
        $schema = $this->getSchema();

        $normalized = $schema->normalize($data);

        $this->makeQuery()->update($normalized);

        /** @var Auth $auth */
        $auth = App::load(Auth::class);

        $schema->onUpdate($auth->userId(), $normalized);

        return Response::ok();
    }

}