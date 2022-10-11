<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Haskel\TelegramBundle\Constant\RequestAttribute;
use Haskel\TelegramBundle\Telegram\Api\TelegramApi;
use Haskel\TelegramBundle\Telegram\Type\Update\Update;
use Stringable;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::VIEW)]
class ViewListener
{
    public function __construct(
        private TelegramApi $telegram,
    ) {
    }

    public function __invoke(ViewEvent $event): void
    {
        /** @var Update $update */
        $update = $event->getRequest()->attributes->get(RequestAttribute::UPDATE);

        if (!$update) {
            return;
        }

        $result = $event->getControllerResult();

        match (true) {
            is_scalar($result) || $result instanceof Stringable
                => $this->telegram->message->sendMessage(
                    (string)$update->message->chat->id,
                    (string)$result
            ),
            is_object($result) && UpdatedMessage::class === $result::class
                => $this->telegram->message->editMessageText(
                    $result->chatId,
                    $result->messageId,
                    $result->text,
            ),
            default
                => $event->setResponse($event->getControllerResult()),
        };

        $event->setResponse(new JsonResponse());
    }
}
