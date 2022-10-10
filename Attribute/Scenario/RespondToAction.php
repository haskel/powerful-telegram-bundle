<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute\Scenario;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RespondToAction
{
    public function __construct(
        public string $actionName,
    ) {
    }
}
