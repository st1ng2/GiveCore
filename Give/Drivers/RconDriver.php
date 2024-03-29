<?php

namespace Flute\Modules\GiveCore\Give\Drivers;

use Flute\Core\Database\Entities\Server;
use Flute\Core\Database\Entities\User;
use Flute\Modules\GiveCore\Contracts\DriverInterface;
use Flute\Modules\GiveCore\Exceptions\BadConfigurationException;
use Flute\Modules\GiveCore\Exceptions\GiveDriverException;
use Flute\Modules\GiveCore\Exceptions\UserSocialException;
use xPaw\SourceQuery\SourceQuery;

class RconDriver implements DriverInterface
{
    public function deliver(User $user, Server $server, array $additional = []): bool
    {
        if (!$server->rcon)
            throw new BadConfigurationException("Server $server->name rcon empty");

        if (!isset ($additional['command']))
            throw new BadConfigurationException('command');

        $command = $additional['command'];
        $steam = false;

        if (preg_match('/{{steam32}}|{{steam64}}/i', $command)) {
            foreach ($user->socialNetworks as $socialNetwork) {
                if ($socialNetwork->socialNetwork->key === 'Steam') {
                    $steam = $socialNetwork->value;
                }
            }

            if (!$steam)
                throw new UserSocialException("Steam");
        }

        try {
            $query = $this->sendCommand($server->ip, $server->port, $server->rcon, $this->replace($command, $steam));

            return true;
        } catch (\Exception $e) {
            throw new GiveDriverException($e->getMessage());
        } finally {
            $query->Disconnect();
        }

        return false;
    }

    public function alias(): string
    {
        return 'rcon';
    }

    protected function replace(string $command, $steam): string
    {
        $steam32 = '';
        $steam64 = '';

        if ($steam) {
            $steamClass = steam()->steamid($steam);
            $steam32 = $steamClass->RenderSteam2();
            $steam64 = $steamClass->ConvertToUInt64();
        }

        return str_replace([
            '{{steam32}}',
            '{{steam64}}',
        ], [
            $steam32,
            $steam64
        ], $command);
    }

    protected function sendCommand(string $ip, int $port, string $rcon, string $command): SourceQuery
    {
        $Query = new SourceQuery;

        $Query->Connect($ip, $port);
        $Query->SetRconPassword($rcon);

        // We don't need a get result from the server
        $Query->Rcon($rcon);

        return $Query;
    }
}