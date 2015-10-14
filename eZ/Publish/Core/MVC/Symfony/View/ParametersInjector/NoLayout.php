<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'noLayout' boolean based on the value of the 'layout' attribute.
 */
class NoLayout implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectCustomParameters'];
    }

    public function injectCustomParameters(FilterViewParametersEvent $event)
    {
        $parameters = $event->getBuilderParameters();

        $event->getParameterBag()->set(
            'noLayout',
            isset($parameters['layout']) ? !(bool) $parameters['layout'] : false
        );
    }
}
