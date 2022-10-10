<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::EXCEPTION)]
class ExceptionListener
{
    public function __construct(private LoggerInterface $logger) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $this->logger->error('logged exception', ['exception' => $event->getThrowable()]);

        $event->setResponse(new JsonResponse(null, 204));
    }
}
