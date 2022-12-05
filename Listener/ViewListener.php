<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Listener;

use Generator;
use Haskel\Telegram\Type\ChatAction;
use Haskel\Telegram\Type\InlineKeyboardMarkup;
use Haskel\Telegram\Type\InlineQueryResult\InlineQueryResultArticle;
use Haskel\Telegram\Type\InlineQueryResult\InputTextMessageContent;
use Haskel\Telegram\Type\KeyboardMarkup;
use Haskel\Telegram\Type\Update\InlineQueryUpdate;
use Haskel\TelegramBundle\Constant\RequestAttribute;
use Haskel\Telegram\Api\TelegramApi;
use Haskel\Telegram\Type\Update\Update;
use Haskel\TelegramBundle\Model\InlineQueryAnswer;
use Haskel\TelegramBundle\Model\KeyboardMessage;
use Haskel\TelegramBundle\TelegramApiPool;
use Haskel\TelegramBundle\Model\UpdatedMessage;
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

        $chatId = $update->message?->chat?->id
                  ?? $update->editedMessage?->chat?->id
                  ?? $update->channelPost?->chat?->id
                  ?? $update->editedChannelPost?->chat?->id
                  ?? $update->inlineQuery?->from?->id
                  ?? $update->chosenInlineResult?->from?->id
                  ?? $update->callbackQuery?->from?->id
                  ?? $update->shippingQuery?->from?->id
                  ?? $update->preCheckoutQuery?->from?->id
                  ?? $update->poll?->chat?->id
                  ?? $update->pollAnswer?->user?->id;


        match (true) {
            $result instanceof ChatAction
                => $api->message->sendChatAction(
                    $chatId,
                    $result
                ),

            is_scalar($result) || $result instanceof Stringable
                => $api->message->sendMessage(
                    $chatId,
                    (string)$result
                ),

            is_object($result) && UpdatedMessage::class === $result::class
                => $api->message->editMessageText(
                    $result->chatId,
                    $result->messageId,
                    $result->text,
                ),

            is_array($result) && ($update instanceof InlineQueryUpdate)
                => $api->inline->answerInlineQuery(
                    $update->inlineQuery->id,
                    $this->processInlineResult($result)
                ),

            is_object($result) && InlineQueryAnswer::class === $result::class
                => $api->inline->answerInlineQuery(
                    $result->inlineQueryId,
                    $result->results,
                ),

            is_object($result) && $result instanceof KeyboardMarkup
                => $api->message->sendMessage(
                    $chatId,
                    '.',
                    replyMarkup: $result
                ),

            is_object($result) && $result instanceof KeyboardMessage
                => $this->updateKeyboardMessage($chatId, $api, $result, $update),

            default
                => $event->setResponse(new JsonResponse($event->getControllerResult())),
        };
    }

    private function processInlineResult(array $result): array
    {
        $processed = [];
        foreach ($result as $key => $item) {
            if ($item instanceof InlineQueryResultArticle) {
                $processed[] = $item;
                continue;
            }

            if (is_scalar($item)) {
                $processed[] = new InlineQueryResultArticle(
                    (string)$key,
                    (string)$item,
                    new InputTextMessageContent((string)$item)
                );
                continue;
            }
        }

        return $processed;
    }

    private function updateKeyboardMessage(
        $chatId,
        TelegramApi $api,
        KeyboardMessage $result,
        Update $update
    ) {
        $messageId = $update->message->messageId;


        $api->message->editMessageText(
            $chatId,
            $messageId,
            $result->text,
        );

        $api->message->editMessageReplyMarkup(
            $chatId,
            $messageId,
            replyMarkup: $result->keyboardMarkup
        );
    }
}
