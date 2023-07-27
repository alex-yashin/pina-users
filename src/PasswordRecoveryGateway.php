<?php

namespace PinaUsers;

use Pina\Data\Schema;
use Pina\DB\TokenGeneratorTrait;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;
use Pina\Types\TokenType;

use function Pina\__;

class PasswordRecoveryGateway extends TableDataGateway
{
    use TokenGeneratorTrait;

    protected static $table = "password_recovery";

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', TokenType::class);
        $schema->setPrimaryKey(['id']);
        $schema->add('user_id', __('Пользователь'), IntegerType::class);
        $schema->addCreatedAt(__('Дата создания'));
        $schema->addKey(['created_at']);
        return $schema;
    }

}
