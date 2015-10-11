<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Configures the PagerFanta instance of a QueryTypeView, if enabled.
 */
class QueryTypePager implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [Events::VIEW_PARAMETERS_INJECTION => 'configurePager'];
    }

    public function configurePager(ViewParametersFilterEvent $event)
    {
        if (!($view = $event->getView()) instanceof QueryTypeView) {
            return;
        }

        if ($view->getConfigHash()['enabled_pager'] === false) {
            return;
        }
    }
}
