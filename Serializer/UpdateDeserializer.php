<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\Serializer;

use Haskel\Telegram\Type\Update\CallbackQueryUpdate;
use Haskel\Telegram\Type\Update\ChannelPostUpdate;
use Haskel\Telegram\Type\Update\ChatJoinRequestUpdate;
use Haskel\Telegram\Type\Update\ChatMemberUpdate;
use Haskel\Telegram\Type\Update\ChosenInlineResultUpdate;
use Haskel\Telegram\Type\Update\EditedChannelPostUpdate;
use Haskel\Telegram\Type\Update\EditedMessageUpdate;
use Haskel\Telegram\Type\Update\InlineQueryUpdate;
use Haskel\Telegram\Type\Update\MessageUpdate;
use Haskel\Telegram\Type\Update\MyChatMemberUpdate;
use Haskel\Telegram\Type\Update\PollAnswerUpdate;
use Haskel\Telegram\Type\Update\PollUpdate;
use Haskel\Telegram\Type\Update\PreCheckoutQueryUpdate;
use Haskel\Telegram\Type\Update\ShippingQueryUpdate;
use Haskel\Telegram\Type\Update\Update;

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
