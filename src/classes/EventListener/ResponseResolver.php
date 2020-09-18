<?php


namespace App\EventListener;


use App\Controllers\BasicController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseResolver implements EventSubscriberInterface
{
    private ContainerInterface $container;

    private ?BasicController $calledController;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::VIEW => 'onKernelView',
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    public function onKernelView(ViewEvent $event)
    {
        if ($this->calledController === null || $event->getResponse() !== null) {
            return;
        }

        $event->setResponse(new Response($this->calledController->getView()->render()));
    }

    public function onKernelController(ControllerEvent $event)
    {
        /** @var BasicController $controller */
        if (!is_array($event->getController())) {
            return;
        }
        $controller = $event->getController()[0];

        if (!$event->isMasterRequest() || !$controller instanceof BasicController) {
            return;
        }
        $this->calledController = $controller;

        $controller->init();
        $controller->setViewTemplate($event->getController()[1]);
        $controller->callFormats();
        $controller->callBeforeActions($event->getController()[1], $event->getRequest()->attributes->get('_route_params') ?? []);
    }
}
