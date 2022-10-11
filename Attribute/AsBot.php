<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsBot
{
    public function __construct(
        public ?string $name,
    ) {
    }
}
