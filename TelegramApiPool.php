<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Haskel\Telegram\Api\TelegramApi;

class TelegramApiPool
{
    /**
     * @var array<string, TelegramApi>
     */
    private array $apis = [];

    public function add(TelegramApi $api): void
    {
        $this->apis[$api->botName] = $api;
    }

    public function remove(string $name): void
    {
        if (isset($this->apis[$name])) {
            unset($this->apis[$name]);
        }
    }

    public function get(string $name): ?TelegramApi
    {
        return $this->apis[$name] ?? null;
    }
}
