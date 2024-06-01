<?php

namespace Flute\Modules\GiveCore\Give\Drivers;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Modules\GiveCore\Contracts\DriverInterface;

class AdminDriver implements DriverInterface
{
    public function deliver(User $user, Server $server, array $additional = [], ?int $timeId = null): bool
    {
        return true;
    }

    public function alias() : string
    {
        return 'admin';
    }
}