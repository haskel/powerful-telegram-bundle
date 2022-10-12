<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Routing;

use Haskel\Telegram\Type\Update\Update;
use Haskel\TelegramBundle\Model\Scenario;

class ScenarioResolver
{

    public function resolve(string $botName, Update $update): Scenario
    {
        return new Scenario('default');
    }
}
