<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response cache properties.
 */
class CacheViewResponseListener implements EventSubscriberInterface
{
    /**
     * True if view cache is enabled, false if it is not.
     *
     * @var bool
     */
    private $enableViewCache;

    /**
     * True if TTL cache is enabled, false if it is not.
     * @var bool
     */
    private $enableTtlCache;

    /**
     * Default ttl for ttl cache for OK (200) and OK but empty requests (204, 304).
     *
     * @var int
     */
    private $defaultTtl;

    /**
     * Default ttl for any requests not considered Ok, but been marked to be cached anyway (but for shorter time).
     *
     * @var int
     */
    private $defaultErrorTtl;

    public function __construct($enableViewCache, $enableTtlCache, $defaultTtl, $defaultErrorTtl = 10)
    {
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
        $this->defaultTtl = $defaultTtl;
        $this->defaultErrorTtl = $defaultErrorTtl;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        if (!($view = $event->getRequest()->attributes->get('view')) instanceof CachableView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();

        if ($view instanceof LocationValueView && ($location = $view->getLocation()) instanceof Location) {
            $response->headers->set('X-Location-Id', $location->id, false);
        }

        $response->setPublic();
        if ($this->enableTtlCache && !$response->headers->hasCacheControlDirective('s-maxage')) {
            if ($response->isOk() || $response->isEmpty()) {
                $response->setSharedMaxAge($this->defaultTtl);
            } else {
                $response->setSharedMaxAge($this->defaultErrorTtl);
            }
        }
    }
}
