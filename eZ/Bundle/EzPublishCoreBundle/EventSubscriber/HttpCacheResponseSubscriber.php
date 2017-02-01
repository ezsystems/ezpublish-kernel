<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventSubscriber;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response HTTP cache properties.
 */
class HttpCacheResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $dispatcherTagger;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator
     */
    private $responseConfigurator;

    public function __construct(ResponseCacheConfigurator $responseConfigurator, ResponseTagger $dispatcherTagger)
    {
        $this->responseConfigurator = $responseConfigurator;
        $this->dispatcherTagger = $dispatcherTagger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        $view = $event->getRequest()->attributes->get('view');
        if (!$view instanceof CachableView || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();
        $this->responseConfigurator->enableCache($response);
        $this->responseConfigurator->setSharedMaxAge($response);
        $this->dispatcherTagger->tag($this->responseConfigurator, $response, $view);
    }
}
