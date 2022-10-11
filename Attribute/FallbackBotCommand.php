<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class FallbackBotCommand
{
    public const NAME = '@fallback_command';
}
