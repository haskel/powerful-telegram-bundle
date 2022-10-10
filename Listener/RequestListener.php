<?php

declare(strict_types=1);

namespace Haskel\TelegramBundle;

use App\Constant\HappyCustomerBot;
use Haskel\TelegramBundle\Constant\RequestAttribute;
use Haskel\TelegramBundle\Controller\BotController;
use Haskel\TelegramBundle\Storage\UpdateQueue;
use Haskel\TelegramBundle\Telegram\Type\Update\Update;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::REQUEST)]
class RequestListener
{
    public function __construct(
        private UpdateDeserializer $updateDeserializer,
        private BotRouter $router,
        private ScenarioResolver $scenarioResolver,
        private LoggerInterface $logger,
        private UpdateQueue $updateQueue,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->isBotRequest($event->getRequest())) {
            return;
        }

        try {
            $body = $event->getRequest()->getContent();
            $bodyDecoded = json_decode($event->getRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $update = $this->updateDeserializer->deserialize($bodyDecoded);
            if (!$update instanceof Update) {
                return;
            }

            $event->getRequest()->attributes->set(RequestAttribute::UPDATE, $update);

            $controller = $this->getController($event->getRequest());
            if ($controller) {
                $event->getRequest()->attributes->set('_controller', $controller);
            }

            if (true) {
                $this->updateQueue->push($body, $event->getRequest()->attributes->get(RequestAttribute::BOT_NAME));
            }

        } catch (\JsonException $e) {
            $this->logger->warning('Invalid json', ['exception' => $e]);
        }
    }

    private function isBotRequest(Request $request): bool
    {
        return true;
    }

    public function getController(Request $request): ?array
    {
        $botName = $request->attributes->get(RequestAttribute::BOT_NAME);
        $update = $request->attributes->get(RequestAttribute::UPDATE);

        if (null === $update) {
            return null;
        }

        $scenario = $this->scenarioResolver->resolve($botName, $update);
        return $this->router->resolve($botName, $update, $scenario);
    }
}
