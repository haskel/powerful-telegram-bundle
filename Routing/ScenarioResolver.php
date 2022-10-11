<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Haskel\TelegramBundle\Telegram\Type\Update\Update;

class ScenarioResolver
{

    public function resolve(string $botName, Update $update): Scenario
    {
        return new Scenario('default');
    }
}
