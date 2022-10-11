<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

class UpdatedMessage
{
    public function __construct(
        public string $chatId,
        public int $messageId,
        public ?string $text = null,
    ) {
    }
}
