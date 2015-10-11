<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Injects into a View parameters that were collected via the EventDispatcher.
 */
class EventDispatcherInjector implements ParametersInjector
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function injectViewParameters(View $view, array $parameters)
    {
        $event = new ViewParametersFilterEvent($view, $parameters);
        $this->eventDispatcher->dispatch(Events::VIEW_PARAMETERS_INJECTION, $event);
        $view->addParameters($event->getViewParameters());
    }
}
