<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Scenario
{
    public function __construct(
        public string $name,
    ) {
    }
}
