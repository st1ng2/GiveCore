<?php

namespace Flute\Modules\GiveCore\Give\Drivers;

use Flute\Core\Database\Entities\Server;
use Flute\Modules\GiveCore\Exceptions\BadConfigurationException;
use Flute\Modules\GiveCore\Exceptions\UserSocialException;
use Flute\Modules\GiveCore\Support\AbstractDriver;

class VipDriver extends AbstractDriver
{
    public function deliver(\Flute\Core\Database\Entities\User $user, Server $server, array $additional = []): bool
    {
        if (!$server->rcon)
            throw new BadConfigurationException("Server $server->name rcon empty");

        if (!isset ($additional['group']))
            throw new BadConfigurationException('group');

        foreach ($user->socialNetworks as $socialNetwork) {
            if ($socialNetwork->socialNetwork->key === 'Steam') {
                $steam = $socialNetwork->value;
            }
        }

        if (!$steam)
            throw new UserSocialException("Steam");

        $sid = $additional['sid'] ?? 1;

        

        $this->confirm('You already have a <span>VIP</span> group. Buying this will replace it with <span>VIP PREMIUM</span>. Do you agree?');

        // $select = $this->select([ 
        //     [
        //         'value' => 'test3',
        //         'text' => 'Some text'
        //     ],
        //     [
        //         'value' => 'test2',
        //         'text' => 'Some text 2'
        //     ],
        // ], 'select_vip_server');

        return true;
    }

    public function alias(): string
    {
        return 'vip';
    }
}