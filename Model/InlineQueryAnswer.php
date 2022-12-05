<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Model;

class InlineQueryAnswer
{
    public function __construct(
        public string $inlineQueryId,
        public array $results,
    ) {
    }
}
