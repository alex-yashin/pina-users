<?php

namespace PinaUsers\SQL;

use Exception;
use Pina\Types\CheckedEnabledType;
use Pina\Types\StringType;
use PinaUsers\Types\EmailType;
use PinaUsers\Types\PasswordType;

use Pina\Data\Schema;
use Pina\TableDataGateway;

use function Pina\__;

class UserGateway extends TableDataGateway
{
    protected static $table = "user";

    /**
     * @return Schema
     * @throws Exception
     */
    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema->addAutoincrementPrimaryKey();
        $schema->add('email', 'Email', EmailType::class)->setNullable()->setMandatory()->tag('my-profile');
        $schema->add('first_name', __('Имя'), StringType::class)->setMandatory()->setWidth(6)->tag('my-profile');
        $schema->add('last_name', __('Фамилия'), StringType::class)->setMandatory()->setWidth(6)->tag('my-profile');
        $schema->add('password', __('Пароль'), PasswordType::class)->setMandatory();
        $schema->add('enabled', __('Активен'), CheckedEnabledType::class);
        $schema->addCreatedAt(__('Дата создания'));
        $schema->addUniqueKey(['email']);
        return $schema;
    }

    public function selectTitle($alias = 'title')
    {
        return $this->calculate(
            "CONCAT(" . $this->getAlias() . ".first_name, ' ', " . $this->getAlias() . ".last_name)",
            $alias
        );
    }

}