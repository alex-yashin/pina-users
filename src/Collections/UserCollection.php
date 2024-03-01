<?php


namespace PinaUsers\Collections;

use Exception;
use PinaUsers\Hash;
use PinaUsers\Types\RepeatPasswordType;
use PinaUsers\SQL\UserGateway;
use Pina\Data\DataCollection;
use Pina\Data\Schema;

use function Pina\__;

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

    /**
     * @return Schema
     * @throws Exception
     */
    public function getPasswordSchema()
    {
        $schema = parent::getSchema()->only(['password']);
        $schema->add('password2', __('Повторите пароль'), RepeatPasswordType::class);
        return $schema;
    }

    /**
     * @param string $id
     * @param array $data
     * @param array $context
     * @return string
     * @throws Exception
     */
    public function updatePassword(string $id, array $data): string
    {
        $schema = $this->getPasswordSchema();

        $normalized = $this->normalize($data, $schema, $id);

        $normalized['password'] = Hash::make($normalized['password']);

        $this->makeQuery()->whereId($id)->update($normalized);

        $schema->onUpdate($id, $normalized);

        return $id;
    }

}