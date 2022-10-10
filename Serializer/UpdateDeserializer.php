<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Haskel\TelegramBundle\Telegram\Type\Update\CallbackQueryUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\ChannelPostUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\ChatJoinRequestUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\ChatMemberUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\ChosenInlineResultUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\EditedChannelPostUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\EditedMessageUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\InlineQueryUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\MessageUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\MyChatMemberUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\PollAnswerUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\PollUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\PreCheckoutQueryUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\ShippingQueryUpdate;
use Haskel\TelegramBundle\Telegram\Type\Update\Update;

class UpdateDeserializer
{
    public function deserialize(array $body): ?Update
    {
        $type = match (true) {
            isset($body['message']) => MessageUpdate::class,
            isset($body['edited_message']) => EditedMessageUpdate::class,
            isset($body['channel_post']) => ChannelPostUpdate::class,
            isset($body['edited_channel_post']) => EditedChannelPostUpdate::class,
            isset($body['inline_query']) => InlineQueryUpdate::class,
            isset($body['chosen_inline_result']) => ChosenInlineResultUpdate::class,
            isset($body['callback_query']) => CallbackQueryUpdate::class,
            isset($body['shipping_query']) => ShippingQueryUpdate::class,
            isset($body['pre_checkout_query']) => PreCheckoutQueryUpdate::class,
            isset($body['poll']) => PollUpdate::class,
            isset($body['poll_answer']) => PollAnswerUpdate::class,
            isset($body['chat_join_request']) => ChatJoinRequestUpdate::class,
            isset($body['chat_member']) => ChatMemberUpdate::class,
            isset($body['my_chat_member']) => MyChatMemberUpdate::class,

            default => null,
        };

        if (null === $type) {
            return null;
        }

        return call_user_func_array($type ."::buildFromArray", [$body]);
    }
}
