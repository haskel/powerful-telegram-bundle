<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

enum ActionResponseType: string
{
    case Message = 'message';
    case Keyboard = 'keyboard';
    case InlineKeyboard = 'inline_keyboard';
}
