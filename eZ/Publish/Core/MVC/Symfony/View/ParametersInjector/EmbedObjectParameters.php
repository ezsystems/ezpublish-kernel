<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects the 'objectParameters' array as a standalone variable.
 */
class EmbedObjectParameters implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [Events::VIEW_PARAMETERS_INJECTION => 'injectEmbedObjectParameters'];
    }

    public function injectEmbedObjectParameters(ViewParametersFilterEvent $event)
    {
        $viewType = $event->getView()->getViewType();
        if ($viewType == 'embed' || $viewType == 'embed-inline') {
            $builderParameters = $event->getBuilderParameters();
            if (isset($builderParameters['params']['objectParameters']) && is_array($builderParameters['params']['objectParameters'])) {
                $event->getParameterBag()->set('objectParameters', $builderParameters['params']['objectParameters']);
            }
        }
    }
}
