<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Model;

use Haskel\Telegram\Type\Update\Update;

class Scenario
{
    public function __construct(
        public string $name = 'default',
    ) {
    }

    public function getAction(Update $update): string
    {
//        $currentStep = $this->stepLoader->getCurrentStep(
//            $this->botName,
//            $this->getChat($update),
//            $this->getUser($update),
//        );
//
//        $availableTransitions = [];
//        foreach ($currentStep->allowedTransitions() as $transition) {
//            if ($transition->isFit($this->update)) {
//                $availableTransitions[] = $transition;
//            }
//        }

        return '@fallback_action';
    }

    public function isDefault(): bool
    {
        return 'default' === $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
