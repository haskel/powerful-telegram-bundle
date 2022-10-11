<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

class Scenario
{
    public function __construct(
        public string $name = 'default',
    ) {
    }

    public function getAction(): string
    {
        return '@fallback_action';
    }

    public function isDefault(): bool
    {
        return 'default' === $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
