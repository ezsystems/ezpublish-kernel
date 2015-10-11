<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'viewBaseLayout' view parameter, set by the container parameter.
 */
class ViewbaseLayout implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $viewbaseLayout;

    public function __construct($viewbaseLayout)
    {
        $this->viewbaseLayout = $viewbaseLayout;
    }

    public static function getSubscribedEvents()
    {
        return [Events::VIEW_PARAMETERS_INJECTION => 'injectViewbaseLayout'];
    }

    public function injectViewbaseLayout(ViewParametersFilterEvent $event)
    {
        $event->getParameterBag()->set('viewbaseLayout', $this->viewbaseLayout);
    }
}
