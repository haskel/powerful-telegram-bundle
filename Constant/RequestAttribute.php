<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Constant;

class RequestAttribute
{
    public const UPDATE = 'telegram_update';
    public const BOT_NAME = 'telegram_bot_name';
    public const ACTION = 'telegram_controller_action';
    public const ACTION_ARGUMENTS = 'telegram_controller_action_arguments';
}
