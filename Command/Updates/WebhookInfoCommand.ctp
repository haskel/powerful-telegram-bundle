<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Command\Updates;

use Haskel\TelegramBundle\TelegramApiPool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebhookInfoCommand extends Command
{
    public static $defaultName = 'telegram:webhook:info';

    public function __construct(
        private TelegramApiPool $telegramApiPool,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    public function configure(): void
    {
        $this
            ->setDescription('Get information about the current webhook status.')
            ->setHelp('This command allows you to get information about the current webhook status.')
            ->addArgument('botName', InputArgument::REQUIRED, 'Bot name')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $botName = $input->getArgument('botName');

        $api = $this->telegramApiPool->require($botName);

        $api->bot->getWebhookInfo();
    }
}
