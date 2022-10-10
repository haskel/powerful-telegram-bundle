<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Storage;

use Haskel\TelegramBundle\Telegram\Type\Update\Update;

interface UpdateQueue
{
    public function push(string $update): void;
    public function pop(): ?string;
}
