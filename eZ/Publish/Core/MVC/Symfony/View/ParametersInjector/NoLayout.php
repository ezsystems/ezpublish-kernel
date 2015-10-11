<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'noLayout' boolean based on the value of the 'layout' attribute.
 */
class NoLayout implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [Events::VIEW_PARAMETERS_INJECTION => 'injectCustomParameters'];
    }

    public function injectCustomParameters(ViewParametersFilterEvent $event)
    {
        $parameters = $event->getBuilderParameters();

        $event->getParameterBag()->set(
            'noLayout',
            isset($parameters['layout']) ? !(bool) $parameters['layout'] : false
        );
    }
}
