<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'viewBaseLayout' view parameter, set by the container parameter.
 */
class ViewbaseLayout implements EventSubscriberInterface
{
    /** @var string */
    private $pageLayout;

    /** @var string */
    private $viewbaseLayout;

    public function __construct($viewbaseLayout)
    {
        $this->viewbaseLayout = $viewbaseLayout;
    }

    public function setPageLayout($pageLayout)
    {
        $this->pageLayout = $pageLayout;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectViewbaseLayout'];
    }

    public function injectViewbaseLayout(FilterViewParametersEvent $event)
    {
        $event->getParameterBag()->set('viewbaseLayout', $this->viewbaseLayout);
        $event->getParameterBag()->set('pagelayout', $this->pageLayout);
    }
}
