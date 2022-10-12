<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Listener;

use Generator;
use Haskel\Telegram\Type\ChatAction;
use Haskel\TelegramBundle\Constant\RequestAttribute;
use Haskel\Telegram\Api\TelegramApi;
use Haskel\Telegram\Type\Update\Update;
use Haskel\TelegramBundle\TelegramApiPool;
use Haskel\TelegramBundle\UpdatedMessage;
use Stringable;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::VIEW)]
class ViewListener
{
    public function __construct(
        private TelegramApiPool $telegramApiPool,
    ) {
    }

    public function onKernelView(ViewEvent $event): void
    {
        /** @var Update $update */
        $update = $event->getRequest()->attributes->get(RequestAttribute::UPDATE);
        $botName = $event->getRequest()->attributes->get(RequestAttribute::BOT_NAME);

        if (!$update) {
            return;
        }

        $result = $event->getControllerResult();

        if ($result instanceof Generator) {
            foreach ($result as $item) {
                $this->processResponse($item, $botName, $update, $event);
            }
        } else {
            $this->processResponse($result, $botName, $update, $event);
        }

        $event->setResponse(new JsonResponse());
    }

    private function processResponse($result, string $botName, Update $update, ViewEvent $event): void
    {
        $api = $this->telegramApiPool->get($botName);
        if (!$api) {
            return;
        }

        match (true) {
            $result instanceof ChatAction
                => $api->message->sendChatAction(
                    $update->message?->chat?->id,
                    $result
                ),

            is_scalar($result) || $result instanceof Stringable
                => $api->message->sendMessage(
                    (string)$update->message->chat->id,
                    (string)$result
                ),

            is_object($result) && UpdatedMessage::class === $result::class
                => $api->message->editMessageText(
                    $result->chatId,
                    $result->messageId,
                    $result->text,
                ),

            default
                => $event->setResponse($event->getControllerResult()),
        };
    }
}
