<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'viewBaseLayout' view parameter, set by the container parameter.
 */
class ViewbaseLayout implements EventSubscriberInterface
{
    /** @var string */
    private $viewbaseLayout;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct($viewbaseLayout, ConfigResolverInterface $configResolver)
    {
        $this->viewbaseLayout = $viewbaseLayout;
        $this->configResolver = $configResolver;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectViewbaseLayout'];
    }

    private function getPageLayout(): string
    {
        return $this->configResolver->hasParameter('page_layout')
            ? $this->configResolver->getParameter('page_layout')
            : $this->configResolver->getParameter('pagelayout');
    }

    public function injectViewbaseLayout(FilterViewParametersEvent $event)
    {
        $pageLayout = $this->getPageLayout();

        $event->getParameterBag()->set('view_base_layout', $this->viewbaseLayout);
        // @deprecated since 8.0. Use `page_layout` instead
        $event->getParameterBag()->set('pagelayout', $pageLayout);
        $event->getParameterBag()->set('page_layout', $pageLayout);
    }
}
