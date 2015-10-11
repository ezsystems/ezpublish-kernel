<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Values\Content\Location;
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
     * @var
     */
    private $enableTtlCache;

    /**
     * Default ttl for ttl cache.
     *
     * @var int
     */
    private $defaultTtl;

    public function __construct($enableViewCache, $enableTtlCache, $defaultTtl)
    {
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
        $this->defaultTtl = $defaultTtl;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        if (!($view = $event->getRequest()->attributes->get('view')) instanceof View) {
            return;
        }

        if (!$this->enableViewCache) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($view instanceof LocationValueView && ($location = $view->getLocation()) instanceof Location) {
            $response->headers->set('X-Location-Id', $location->id, false);
        }

        $response->setPublic();
        if ($this->enableTtlCache) {
            $response->setSharedMaxAge($this->defaultTtl);
        }
        // Make the response vary against X-User-Hash header ensures that an HTTP
        // reverse proxy caches the different possible variations of the
        // response as it can depend on user role for instance.
        if ($request->headers->has('X-User-Hash')) {
            $response->setVary('X-User-Hash');
        }
//        if ($lastModified != null) {
//            $response->setLastModified($lastModified);
//        }
    }
}
