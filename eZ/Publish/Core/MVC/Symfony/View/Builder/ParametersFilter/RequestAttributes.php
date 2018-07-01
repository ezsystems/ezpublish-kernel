<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder\ParametersFilter;

use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;

/**
 * Collects parameters for the ViewBuilder from the Request.
 */
class RequestAttributes implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'addRequestAttributes'];
    }

    /**
     * Adds all the request attributes to the parameters.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $e
     */
    public function addRequestAttributes(FilterViewBuilderParametersEvent $e)
    {
        $parameterBag = $e->getParameters();
        $parameterBag->add($e->getRequest()->attributes->all());

        // maybe this should be in its own listener ? The ViewBuilder needs it.
        if (!$parameterBag->has('viewType')) {
            $parameterBag->add(['viewType' => null]);
        }
    }
}
