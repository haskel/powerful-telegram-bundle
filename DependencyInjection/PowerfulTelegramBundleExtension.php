<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\DependencyInjection;

use Haskel\TelegramBundle\Attribute\AsBot;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PowerfulTelegramBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerAttributeForAutoconfiguration(
            AsBot::class,
            static function (
                ChildDefinition $definition,
                AsBot $attribute,
                ReflectionClass $reflector
            ) {
                $tagAttributes = get_object_vars($attribute);
                $definition->addTag('bot.telegram', $tagAttributes);
                $definition->addTag('controller.service_arguments');
            }
        );
    }
}
