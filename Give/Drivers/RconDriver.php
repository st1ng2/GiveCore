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
    public function deliver(User $user, Server $server, array $additional = [], ?int $timeId = null): bool
    {
        if (!$server->rcon)
            throw new BadConfigurationException("Server $server->name rcon empty");

        if (!isset($additional['command']))
            throw new BadConfigurationException('command');

        $commands = explode(';', $additional['command']);
        $steam = false;

        if (preg_match('/{{steam32}}|{{steam64}}|{{accountId}}/i', $additional['command'])) {
            $steam = $user->getSocialNetwork('Steam') ?? $user->getSocialNetwork('HttpsSteam');

            if (!$steam)
                throw new UserSocialException("Steam");

            $steam = $steam->value;
        }

        $query = new SourceQuery();

        try {
            $query->Connect($server->ip, $server->port, 3, ($server->mod == 10) ? SourceQuery::GOLDSOURCE : SourceQuery::SOURCE);
            $query->SetRconPassword($server->rcon);

            foreach ($commands as $command) {
                $command = trim($command);
                if (empty($command)) {
                    continue;
                }
                $this->sendCommand($query, $this->replace($command, $steam, $user));
            }
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

    protected function replace(string $command, $steam, User $user): string
    {
        $steam32 = '';
        $steam64 = '';
        $accountId = '';

        if ($steam) {
            $steamClass = steam()->steamid($steam);
            $steam32 = $steamClass->RenderSteam2();
            $steam64 = $steamClass->ConvertToUInt64();
            $accountId = $steamClass->GetAccountID();
        }

        return str_replace([
            '{{steam32}}',
            '{{steam64}}',
            '{{accountId}}',
            '{{login}}',
            '{{name}}',
            '{{email}}',
            '{{uri}}'
        ], [
            $steam32,
            $steam64,
            $accountId,
            $user->login,
            $user->name,
            $user->email,
            $user->uri
        ], $command);
    }

    protected function sendCommand(SourceQuery $query, string $command): void
    {
        $query->Rcon($command);
    }
}
