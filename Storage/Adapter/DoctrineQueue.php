<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Storage\Adapter;

use Doctrine\DBAL\Connection;
use Haskel\TelegramBundle\Serializer\UpdateDeserializer;
use Haskel\TelegramBundle\Storage\UpdateQueue;
use Haskel\Telegram\Type\Update\Update;

class DoctrineQueue implements UpdateQueue
{
    public function __construct(
        private string $botName,
        private Connection $connection,
        private $tableName = 'telegram_update_queue',
    ) {
    }

    public function push(string $update): void
    {
        $this->connection->insert(
            'telegram_update_queue', [
                'botName' => $this->botName,
                'payload' => $update,
            ]
        );
    }

    public function pop(): ?string
    {
        $update = $this->connection->fetchFirstColumn(
            'SELECT payload FROM telegram_update_queue WHERE botName = ? LIMIT 1',
            [$this->botName]
        );

        if ($update) {
            $this->connection->delete(
                'telegram_update_queue',
                ['botName' => $this->botName, 'update' => $update]
            );
        }

        return $update;
    }
}
