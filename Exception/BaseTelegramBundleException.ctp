<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Exception;

use RuntimeException;

class BaseTelegramBundleException extends RuntimeException implements TelegramBundleException
{
}
