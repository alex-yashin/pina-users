<?php

namespace PinaUsers\SQL;

use Exception;
use Pina\Data\Schema;
use Pina\DB\TokenGeneratorTrait;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;
use Pina\Types\TokenType;

use function Pina\__;

class PasswordRecoveryGateway extends TableDataGateway
{
    use TokenGeneratorTrait;

    public function getTable(): string
    {
        return "password_recovery";
    }

    /**
     * @throws Exception
     */
    public function getSchema(): Schema
    {
        $schema = parent::getSchema();
        $schema->add('id', 'ID', TokenType::class);
        $schema->setPrimaryKey(['id']);
        $schema->add('user_id', __('Пользователь'), IntegerType::class);
        $schema->addCreatedAt(__('Дата создания'));
        $schema->addKey(['created_at']);
        return $schema;
    }

    public function whereExpired()
    {
        return $this->where($this->getAlias().".created_at < date_sub(NOW(), INTERVAL 1 DAY)");
    }

}
