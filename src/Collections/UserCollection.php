<?php


namespace PinaUsers\Collections;

use PinaUsers\UserGateway;
use Pina\Data\DataCollection;
use Pina\Data\Schema;

class UserCollection extends DataCollection
{
    function makeQuery()
    {
        return UserGateway::instance();
    }

    public function getSchema(): Schema
    {
        return parent::getSchema()->forgetField('password');
    }

    public function getListSchema(): Schema
    {
        return parent::getListSchema()->forgetField('password');
    }

    public function getFilterSchema(): Schema
    {
        return $this->getListSchema()->forgetStatic()->setNullable()->setMandatory(false);
    }
}