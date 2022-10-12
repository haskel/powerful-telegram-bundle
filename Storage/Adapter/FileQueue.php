<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Storage\Adapter;

use Haskel\TelegramBundle\Serializer\UpdateDeserializer;
use Haskel\TelegramBundle\Storage\UpdateQueue;
use Haskel\Telegram\Type\Update\Update;

class FileQueue implements UpdateQueue
{
    public function __construct(
        private string $path,
        private UpdateDeserializer $deserializer
    ) {
    }

    public function push(string $update): void
    {
        file_put_contents($this->path, $update . PHP_EOL, FILE_APPEND);
    }

    public function pop(): ?string
    {
        $file = fopen($this->path, 'rb+');

        if (!$file) {
            touch($this->path);
            $file = fopen($this->path, 'rb+');
        }

        $line = fgets($file);

        if (!$line) {
            return null;
        }

        $updateData = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

        $update = $this->deserializer->deserialize($updateData);

        ftruncate($file, 0);
        fclose($file);

        return $update;
    }
}
