<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookController
{
    public function webhook(): JsonResponse
    {
        return new JsonResponse(['ok' => true]);
    }
}
