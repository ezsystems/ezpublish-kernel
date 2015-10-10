<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response cache properties.
 */
class BlockCacheResponseListener implements EventSubscriberInterface
{
    /**
     * True if view cache is enabled, false if it is not.
     *
     * @var bool
     */
    private $enableViewCache;

    public function setEnableViewCache($enableViewCache)
    {
        $this->enableViewCache = $enableViewCache;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['configureBlockCache', -10]];
    }

    public function configureBlockCache(FilterResponseEvent $event)
    {
        $view = $event->getRequest()->attributes->get('view');
        if (!$view instanceof CachableView || !$view instanceof BlockView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();
        $response->setPublic();

        $cacheSettings = $event->getRequest()->attributes->get('cacheSettings', []);

        if (isset($cacheSettings['smax-age']) && is_int($cacheSettings['smax-age'])) {
            $response->setSharedMaxAge((int) $cacheSettings['smax-age']);
        }

        if (isset($cacheSettings['max-age']) && is_int($cacheSettings['max-age'])) {
            $response->setMaxAge((int) $cacheSettings['max-age']);
        }
    }
}
