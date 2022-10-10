<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle\DependencyInjection;

use Haskel\TelegramBundle\Attribute\Action;
use Haskel\TelegramBundle\Attribute\BotCommand;
use Haskel\TelegramBundle\Attribute\FallbackAction;
use Haskel\TelegramBundle\Attribute\FallbackBotCommand;
use Haskel\TelegramBundle\Attribute\InlineQuery;
use Haskel\TelegramBundle\Attribute\Scenario;
use ReflectionClass;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BotCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $bots = $container->findTaggedServiceIds('bot.telegram');

        $scenarioTemplate = [
            'commands' => [],
            'actions' => [],
        ];

        $configurationTemplate = [
            'enabled' => true,
            'commands' => [],
            'actions' => [],
            'scenarios' => [
                'default' => $scenarioTemplate,
            ],
            'webhook' => null,
        ];


        $botsConfiguration = [
            'bots' => [
            ],
        ];

        foreach ($bots as $class => $arguments) {
            foreach ($arguments as $argument) {
                $botName = $arguments[0]['name'] ?? $arguments['name'] ?? 'default';

                if (!isset($botsConfiguration['bots'][$botName])) {
                    $botsConfiguration['bots'][$botName] = $configurationTemplate;
                }

                $classScenarioName = 'default';
                $hasOnlyOneScenario = false;

                $reflection = new ReflectionClass($class);
                foreach ($reflection->getAttributes() as $attribute) {
                    switch ($attribute->getName()) {
                        case Scenario::class:
                            $scenarioName = $attribute->getArguments()[0]
                                            ?? $attribute->getArguments()['name']
                                            ?? 'default';

                            $botsConfiguration['bots'][$botName]['scenarios'][$scenarioName] = $scenarioTemplate;
                            $hasOnlyOneScenario = true;
                            $classScenarioName = $scenarioName;
                            break;
                    }
                }

                foreach ($reflection->getMethods() as $reflectionMethod) {
                    foreach ($reflectionMethod->getAttributes() as $attribute) {
                        switch ($attribute->getName()) {
                            case Action::class:
                                $this->checkIfPublic($reflectionMethod);

                                $attributeArguments = $attribute->getArguments();

                                $actionName = $attributeArguments[0]
                                              ?? $attributeArguments['name']
                                              ?? $reflectionMethod->getName();

                                $scenarioName = $hasOnlyOneScenario
                                              ? $classScenarioName
                                              : $attributeArguments[1] ?? $attributeArguments['scenario'] ?? 'default';

                                $botsConfiguration['bots'][$botName]['scenarios'][$scenarioName]['actions'][$actionName] = [$class, $reflectionMethod->getName()];

                                break;

                            case FallbackAction::class:
                                $this->checkIfPublic($reflectionMethod);

                                $attributeArguments = $attribute->getArguments();

                                $actionName = '@fallback_action';

                                $scenarioName = $hasOnlyOneScenario
                                              ? $classScenarioName
                                              : $attributeArguments[1] ?? $attributeArguments['scenario'] ?? 'default';

                                $botsConfiguration['bots'][$botName]['scenarios'][$scenarioName]['actions'][$actionName] = [$class, $reflectionMethod->getName()];

                                break;

                            case BotCommand::class:
                                $this->checkIfPublic($reflectionMethod);

                                $attributeArguments = $attribute->getArguments();

                                $commandName = $attributeArguments[0]
                                               ?? $attributeArguments['name']
                                               ?? $reflectionMethod->getName();

                                $botsConfiguration['bots'][$botName]['commands'][$commandName] = [$class, $reflectionMethod->getName()];

                                break;

                            case FallbackBotCommand::class:
                                $this->checkIfPublic($reflectionMethod);

                                $botsConfiguration['bots'][$botName]['commands']['@fallback_command'] = [$class, $reflectionMethod->getName()];

                                break;

                            case InlineQuery::class:
                                $this->checkIfPublic($reflectionMethod);

                                $attributeArguments = $attribute->getArguments();

                                $actionName = '@inline_query';

                                $scenarioName = $hasOnlyOneScenario
                                              ? $classScenarioName
                                              : $attributeArguments[1] ?? $attributeArguments['scenario'] ?? 'default';

                                $botsConfiguration['bots'][$botName]['scenarios'][$scenarioName]['actions'][$actionName] = [$class, $reflectionMethod->getName()];

                                break;
                        }
                    }
                }
            }
        }

        $fileName = $container->getParameter('kernel.cache_dir') . '/haskel_bot.php';

        $cache = (new ConfigCacheFactory($container->getParameter('kernel.debug')))->cache(
            $fileName,
            function (ConfigCacheInterface $cache) use ($botsConfiguration) {
                $cache->write(
                    "<?php\nreturn " .
                    var_export($botsConfiguration, true) .
                    "\n;"
                );
            }
        );
    }

    private function checkIfPublic(\ReflectionMethod $reflectionMethod)
    {
        if (!$reflectionMethod->isPublic()) {
            throw new \LogicException(sprintf('Method %s::%s must be public', $reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName()));
        }
    }
}
