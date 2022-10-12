<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('powerful_telegram');

        $treeBuilder->getRootNode()
                ->children()
                    ->scalarNode('config_cache_file')->defaultNull()->end()
//                    ->arrayNode('queue')
//                    ->arrayPrototype()
//                        ->children()
//                            ->scalarNode('file')->defaultNull()->end()
//                        ->end()
//                    ->end()
                    ->arrayNode('bots')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('token')->isRequired()->end()
                                ->scalarNode('api_url')->defaultNull()->end()
                                ->scalarNode('webhook_url')->defaultNull()->end()
                                ->scalarNode('webhook_certificate')->defaultNull()->end()
                                ->integerNode('webhook_max_connections')->defaultValue(40)->end()
                                ->enumNode('webhook_allowed_updates')
                                    ->values(['message', 'edited_message', 'channel_post', 'edited_channel_post', 'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll', 'poll_answer', 'my_chat_member', 'chat_member'])
                                    ->defaultNull()
                                ->end()
                                ->scalarNode('webhook_ip_address')->defaultNull()->end()
                            ->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}