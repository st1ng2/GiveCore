<?php

namespace Flute\Modules\GiveCore\Contracts;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;

interface DriverInterface
{
    /**
     * Delivers a product to the some user.
     * Must return a bool or Exception if was error 
     * 
     * @return bool
     * 
     * @throws \Exception
     */
    public function deliver( User $user, Server $server, array $additional = [] ) : bool;

    /**
     * Get the alias name for the system
     * 
     * @return string
     */
    public function alias() : string;
}