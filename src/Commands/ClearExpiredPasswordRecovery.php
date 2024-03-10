<?php

namespace PinaUsers\Commands;

use Pina\Command;
use PinaUsers\SQL\PasswordRecoveryGateway;

class ClearExpiredPasswordRecovery extends Command
{
    protected function execute($input = '')
    {
        PasswordRecoveryGateway::instance()->whereExpired()->delete();
    }
}