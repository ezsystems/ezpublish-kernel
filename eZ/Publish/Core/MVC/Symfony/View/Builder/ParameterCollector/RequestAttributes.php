<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder\ParameterCollector;

use eZ\Publish\Core\MVC\Symfony\View\Event\ViewBuilderParameterCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\View\Events as ViewEvents;

/**
 * Collects parameters for the ViewBuilder from the Request.
 */
class RequestAttributes implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ViewEvents::BUILDER_PARAMETER_COLLECTION => 'addRequestAttributes'];
    }

    /**
     * Adds all the request attributes to the parameters.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\ViewBuilderParameterCollectionEvent $e
     */
    public function addRequestAttributes(ViewBuilderParameterCollectionEvent $e)
    {
        $parameterBag = $e->getParameters();
        $parameterBag->add($e->getRequest()->attributes->all());

        // maybe this should be in its own listener ? The ViewBuilder needs it.
        if (!$parameterBag->has('viewType')) {
            $parameterBag->add(['viewType' => null]);
        }
    }
}
