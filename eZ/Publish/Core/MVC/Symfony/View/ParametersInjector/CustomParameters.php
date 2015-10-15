<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the contents of the 'params' array passed to the controller as view parameters.
 */
class CustomParameters implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectCustomParameters'];
    }

    public function injectCustomParameters(FilterViewParametersEvent $event)
    {
        $builderParameters = $event->getBuilderParameters();

        if (isset($builderParameters['params']) && is_array($builderParameters['params'])) {
            $customParameters = $builderParameters['params'];
            $event->getParameterBag()->add($customParameters);
        }
    }
}
