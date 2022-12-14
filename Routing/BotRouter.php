<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Routing;

use Haskel\Telegram\Type\Update\InlineQueryUpdate;
use Haskel\TelegramBundle\Attribute\FallbackAction;
use Haskel\TelegramBundle\Attribute\FallbackBotCommand;
use Haskel\Telegram\Type\Update\Update;
use Haskel\TelegramBundle\Attribute\InlineQuery;
use Haskel\TelegramBundle\Model\Scenario;
use Psr\Log\LoggerInterface;

class BotRouter
{
    private array $router = [];

    public function __construct(string $cacheFile, private LoggerInterface $logger) {
        $this->router = include $cacheFile;
    }

    public function resolve(string $botName, Update $update, Scenario $scenario): array
    {
        $command = $update->hasSingleCommand() ? $update->getCommand() : null;

        if ($update instanceof InlineQueryUpdate) {
            $scenarioAction = InlineQuery::NAME;
        } else {
            $scenarioAction = $scenario->getAction($update) ?? FallbackAction::NAME;
        }

        if ($command) {
            $cleanedCommand = str_replace('/', '', $command);

            $controller = $scenario->isDefault()
                ? ($this->router['bots'][$botName]['commands'][$command]
                   ?? $this->router['bots'][$botName]['commands'][$cleanedCommand]
                      ?? $this->router['bots'][$botName]['commands'][FallbackBotCommand::NAME]
                         ?? null)
                : ($this->router[$botName]['scenarios'][$scenario->getName()]['commands'][$command]
                   ?? $this->router[$botName]['scenarios'][$scenario->getName()]['commands'][$cleanedCommand]
                      ?? $this->router[$botName]['scenarios'][$scenario->getName()]['commands'][FallbackBotCommand::NAME]
                         ?? null);

            if (!$controller) {
                $this->logger->warning('command not found', [
                    'bot' => $botName,
                    'scenario' => $scenario->getName(),
                    'command' => $command,
                    'router' => $this->router,
                ]);
            }

            return $controller ?? [];
        }

        return $scenario->isDefault()
            ? ($this->router['bots'][$botName]['scenarios']['default']['actions'][$scenarioAction])
            : ($this->router['bots'][$botName]['scenarios'][$scenario->getName()]['actions'][$scenarioAction]);
    }
}
