<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\DependencyInjection;

use Haskel\Telegram\Api\BotApi;
use Haskel\Telegram\Api\ChatManagementApi;
use Haskel\Telegram\Api\GameApi;
use Haskel\Telegram\Api\InlineApi;
use Haskel\Telegram\Api\MessageApi;
use Haskel\Telegram\Api\PassportApi;
use Haskel\Telegram\Api\PaymentApi;
use Haskel\Telegram\Api\StickerApi;
use Haskel\Telegram\Api\TelegramApi;
use Haskel\Telegram\Api\TelegramRequestCaller;
use Haskel\TelegramBundle\Attribute\AsBot;
use Haskel\TelegramBundle\TelegramApiPool;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PowerfulTelegramExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->addAnnotatedClassesToCompile([
            'Haskel\TelegramBundle\Listener',
            'Haskel\TelegramBundle\Routing',
            'Haskel\TelegramBundle\Serializer',
            'Haskel\TelegramBundle\Serializer\ArgumentResolver',
        ]);

        $container->setDefinition(TelegramApiPool::class, new Definition(TelegramApiPool::class));
        foreach ($config['bots'] as $name => $botConfig) {
            $this->configureBotApi($name, $container, $botConfig);
        }

        $container->registerAttributeForAutoconfiguration(
            AsBot::class,
            static function (
                ChildDefinition $definition,
                AsBot $attribute,
                ReflectionClass $reflector
            ) use ($container) {
                $tagAttributes = get_object_vars($attribute);
                $definition->addTag('bot.telegram', $tagAttributes);
                $definition->addTag('controller.service_arguments');

                foreach ($reflector->getConstructor()?->getParameters() as $parameter) {
                    if ($parameter->getType()?->getName() === TelegramApi::class) {
                        $apiDefinition = $container->getDefinition('haskel.telegram.api.' . $attribute->name);
                        $definition->setArgument("$" . $parameter->getName(), $apiDefinition);
                    }
                }
            }
        );
    }

    private function configureBotApi(string $name, ContainerBuilder $container, array $botConfig): void
    {
        $botServiceId = 'haskel.telegram.api.' . $name;

        $callerDefinition = new Definition(
            TelegramRequestCaller::class,
            [
                $botConfig['token'],
                $botConfig['base_uri'] ?? null,
            ]
        );

        $container->setDefinition('haskel.telegram.api.' . $name . '.caller', $callerDefinition);
        
        $apiDefinition = new Definition(
            TelegramApi::class,
            [
                $name,
                new Definition(BotApi::class, [$callerDefinition]),
                new Definition(ChatManagementApi::class, [$callerDefinition]),
                new Definition(MessageApi::class, [$callerDefinition]),
                new Definition(StickerApi::class, [$callerDefinition]),
                new Definition(GameApi::class, [$callerDefinition]),
                new Definition(InlineApi::class, [$callerDefinition]),
                new Definition(PaymentApi::class, [$callerDefinition]),
                new Definition(PassportApi::class, [$callerDefinition]),
            ]
        );
        $container->setDefinition($botServiceId, $apiDefinition);

        $poolDefinition = $container->getDefinition(TelegramApiPool::class);
        $poolDefinition->addMethodCall('add', [$apiDefinition]);
    }
}
