<?php

namespace PinaUsers\SQL;

use Exception;
use Pina\Types\UnsignedIntegerType;
use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;
use Pina\Types\StringType;
use Pina\Types\TimestampType;
use Pina\Types\TokenType;

use PinaUsers\Auth;

use function Pina\__;

class AuthGateway extends TableDataGateway
{
    public function getTable(): string
    {
        return 'auth';
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
        $userTable = UserGateway::instance()->getTable();
        return [
            [
                $this->getTable(),
                'before insert',
                "SET NEW.expired_at=NEW.created_at + INTERVAL $interval SECOND"
            ],
            [
                $this->getTable(),
                'after insert',
                "UPDATE $userTable SET last_login_at=NOW() WHERE id=NEW.user_id"
            ],
        ];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function add($data)
    {
        $this->adjustDataAndFields($data);

        if (isset($data['ip'])) {
            $ip = $data['ip'];
            unset($data['ip']);
        }

        $q = "INSERT INTO `" . $this->getTable() . "` SET " . $this->makeSetCondition($data);

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
