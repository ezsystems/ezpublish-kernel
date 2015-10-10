<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\View\Renderer as ViewRenderer;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewRendererListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Renderer */
    private $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => 'renderView'];
    }

    public function renderView(GetResponseForControllerResultEvent $event)
    {
        if (!($view = $event->getControllerResult()) instanceof View) {
            return;
        }

        if (!($response = $view->getResponse()) instanceof Response) {
            $response = new Response();
        }

        $response->setContent($this->viewRenderer->render($view));

        $event->setResponse($response);
    }
}
