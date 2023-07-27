<?php

namespace PinaUsers;

use PinaUsers\Types\AccessGroupType;
use PinaUsers\Types\EmailType;
use PinaUsers\Types\PasswordType;

use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\EnabledType;
use Pina\Types\StringType;

use function Pina\__;

class UserGateway extends TableDataGateway
{
    protected static $table = "user";

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema()
    {
        $schema = new Schema();
        $schema->addAutoincrementPrimaryKey('id', 'ID');
        $schema->add('email', 'Email', EmailType::class)->setMandatory();
        $schema->add('first_name', __('Имя'), StringType::class)->setMandatory()->setWidth(6);
        $schema->add('last_name', __('Фамилия'), StringType::class)->setMandatory()->setWidth(6);
        $schema->add('password', __('Пароль'), PasswordType::class)->setMandatory();
        $schema->add('group', __('Группа'), AccessGroupType::class)->setMandatory();
        $schema->add('enabled', __('Активен'), EnabledType::class);
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