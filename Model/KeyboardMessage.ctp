<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Model;

use Haskel\Telegram\Type\KeyboardMarkup;

class KeyboardMessage
{
    public function __construct(
        public string $text,
        public KeyboardMarkup $keyboardMarkup,
        public ?string $parseMode = null,
        public ?bool $disableWebPagePreview = null,
        public ?bool $disableNotification = null,
    ) {
    }
}
