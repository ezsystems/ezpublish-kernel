<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the ID of the view's value objects as view parameters.
 *
 * Required for backward compatibility with custom view controllers that used arguments such as locationId or contentId.
 */
class ValueObjectsIds implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [View\Events::VIEW_PARAMETERS_INJECTION => 'injectValueObjectsIds'];
    }

    public function injectValueObjectsIds(View\Event\ViewParametersFilterEvent $event)
    {
        $view = $event->getView();
        $parameterBag = $event->getParameterBag();

        if ($view instanceof View\LocationValueView) {
            if (($location = $view->getLocation()) instanceof Location) {
                $parameterBag->set('locationId', $view->getLocation()->id);
            }
        }
        if ($view instanceof View\ContentValueView) {
            $parameterBag->set('contentId', $view->getContent()->id);
        }
        if ($view instanceof View\BlockValueView) {
            $parameterBag->set('blockId', $view->getBlock()->id);
        }
    }
}
