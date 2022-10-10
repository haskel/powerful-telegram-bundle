<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Haskel\TelegramBundle\Constant\RequestAttribute;
use Haskel\TelegramBundle\Telegram\Type\CallbackQuery;
use Haskel\TelegramBundle\Telegram\Type\ChannelPost;
use Haskel\TelegramBundle\Telegram\Type\ChatJoinRequest;
use Haskel\TelegramBundle\Telegram\Type\ChatMember;
use Haskel\TelegramBundle\Telegram\Type\ChosenInlineResult;
use Haskel\TelegramBundle\Telegram\Type\EditedChannelPost;
use Haskel\TelegramBundle\Telegram\Type\EditedMessage;
use Haskel\TelegramBundle\Telegram\Type\InlineQuery;
use Haskel\TelegramBundle\Telegram\Type\Message;
use Haskel\TelegramBundle\Telegram\Type\MyChatMember;
use Haskel\TelegramBundle\Telegram\Type\Poll;
use Haskel\TelegramBundle\Telegram\Type\PollAnswer;
use Haskel\TelegramBundle\Telegram\Type\PreCheckoutQuery;
use Haskel\TelegramBundle\Telegram\Type\ShippingQuery;
use Haskel\TelegramBundle\Telegram\Type\Update\Update;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ArgumentResolver implements ArgumentValueResolverInterface
{
    private array $supportedTypes = [
        CallbackQuery::class,
        ChannelPost::class,
        ChatJoinRequest::class,
        ChatMember::class,
        ChosenInlineResult::class,
        EditedChannelPost::class,
        EditedMessage::class,
        InlineQuery::class,
        Message::class,
        MyChatMember::class,
        Poll::class,
        PollAnswer::class,
        PreCheckoutQuery::class,
        ShippingQuery::class,
    ];

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($request->attributes->get(RequestAttribute::UPDATE) === null) {
            return false;
        }


        if ($argument->getType() === Update::class || is_subclass_of($argument->getType(), Update::class)) {
            return true;
        }

        return in_array($argument->getType(), $this->supportedTypes, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var Update $update */
        $update = $request->attributes->get(RequestAttribute::UPDATE);

        if ($argument->getType() === Update::class) {
            yield $update;
        }

        if (is_subclass_of($argument->getType(), Update::class)) {
            if ($argument->getType() !== $update::class) {
                throw new \LogicException(
                    sprintf("You requested a '%s', but the update is an instance of '%s'", $argument->getType(), $update::class)
                );
            }

            yield $update;
        }

        $availableTypes = [
            CallbackQuery::class => $update->callbackQuery,
            ChannelPost::class => $update->channelPost,
            ChatJoinRequest::class => $update->chatJoinRequest,
            ChatMember::class => $update->chatMember,
            ChosenInlineResult::class => $update->chosenInlineResult,
            EditedChannelPost::class => $update->editedChannelPost,
            EditedMessage::class => $update->editedMessage,
            InlineQuery::class => $update->inlineQuery,
            Message::class => $update->message,
            MyChatMember::class => $update->myChatMember,
            Poll::class => $update->poll,
            PollAnswer::class => $update->pollAnswer,
            PreCheckoutQuery::class => $update->preCheckoutQuery,
            ShippingQuery::class => $update->shippingQuery,
        ];

        if (isset($availableTypes[$argument->getType()])) {
            yield $availableTypes[$argument->getType()];
        }
    }
}
