<?php


namespace PinaUsers\Types;


use Pina\TableDataGateway;
use Pina\Types\QueryDirectoryType;
use PinaUsers\SQL\UserGateway;

class UserType extends QueryDirectoryType
{

    protected function makeQuery(): TableDataGateway
    {
        return UserGateway::instance();
    }

}