<?php

namespace PinaUsers;

use Pina\Types\UnsignedIntegerType;
use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;
use Pina\Types\StringType;
use Pina\Types\TimestampType;
use Pina\Types\TokenType;

use function Pina\__;

class AuthGateway extends TableDataGateway
{
    protected static $table = 'auth';

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
        $schema->add('user_agent', __('User Agent'), StringType::class);
        $schema->add('ip', 'IP', UnsignedIntegerType::class);
        $schema->addCreatedAt();
        $schema->add('expired_at', __('Дата устаревания авторизации'), TimestampType::class)
            ->setNullable();
        return $schema;
    }

    public function getTriggers()
    {
        $interval = Auth::EXPIRATION_INTERVAL;
        return [
            [
                $this->getTable(),
                'before insert',
                "SET NEW.expired_at=NEW.created_at + INTERVAL $interval SECOND"
            ],
        ];
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function add($data = array())
    {
        if (isset($data['ip'])) {
            $ip = $data['ip'];
            unset($data['ip']);
        }

        $q = "INSERT INTO `" . $this->getTable() . "` SET " .
            $this->makeSetCondition($data, array_keys($this->getFields()));

        if (isset($ip)) {
            $ip = ip2long($ip) === false ? 0 : $ip;
            $q .= ", `ip` = INET_ATON('$ip')";
        }

        return $this->db->query($q);
    }

    public function whereExpired()
    {
        $interval = Auth::EXPIRATION_INTERVAL;
        return $this->where($this->getAlias() . ".expired_at < NOW() - INTERVAL $interval SECOND");
    }

    public function whereNotExpired()
    {
        $interval = Auth::EXPIRATION_INTERVAL;
        return $this->where($this->getAlias() . ".expired_at >= NOW() - INTERVAL $interval SECOND");
    }

    public function renew()
    {
        $interval = Auth::EXPIRATION_INTERVAL;
        return $this->updateOperation($this->getAlias() . ".expired_at = NOW() + INTERVAL $interval SECOND");
    }

    public function userId()
    {
        return $this->value('user_id');
    }
}
