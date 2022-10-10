<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag('routing.loader')]
class RouteLoader extends Loader
{
    public function load($resource, string $type = null): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add(
            'webhook',
            new Route(
                '/webhook/{telegram_bot_name}',
                ['_controller' => 'App\Controller\BotController::webhook',]
            )
        );

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'haskelbot' === $type;
    }
}
