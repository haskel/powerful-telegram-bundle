<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute\Scenario;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RespondToPhrase
{
    public function __construct(
        public string $phrase,
    ) {
    }
}
