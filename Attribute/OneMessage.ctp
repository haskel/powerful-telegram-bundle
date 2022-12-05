<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class OneMessage
{
}
