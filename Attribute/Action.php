<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Action
{
    public function __construct(
        public ?string $name = null,
        public ?string $scenario = null,
        public ?ActionResponseType $respond = null,
    ) {
    }
}
