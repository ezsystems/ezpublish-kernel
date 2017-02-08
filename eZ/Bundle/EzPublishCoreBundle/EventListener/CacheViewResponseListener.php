<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\RelationView;
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
        if (!($view = $event->getRequest()->attributes->get('view')) instanceof CachableView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();

        // Tag response so it can be invalidated by tag/key.
        if ($tags = $this->getTags($view)) {
            $response->headers->set('xkey', $tags, false);
        }

        $response->setPublic();
        if ($this->enableTtlCache && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->defaultTtl);
        }
    }

    /**
     * Generate tags relevant for a given view.
     *
     * See doc/specifications/cache/multi_tagging.md
     *
     * @param $view
     *
     * @return array
     */
    private function getTags($view)
    {
        if ($view instanceof LocationValueView && ($location = $view->getLocation()) instanceof Location) {
            $contentInfo = $location->getContentInfo();
            $tags = [
                    'content-' . $location->contentId,
                    'location-' . $location->id,
                    'parent-' . $location->parentLocationId,
                    'content-type-' . $contentInfo->contentTypeId,
            ] + array_map(
                    function ($pathItem) {
                        return 'path-' . $pathItem;
                    },
                    $location->path
            );

            if ($location->id != $contentInfo->mainLocationId) {
                $tags[] = 'location-' . $contentInfo->mainLocationId;
            }
        } elseif ($view instanceof ContentValueView && ($content = $view->getContent()) instanceof Content) {
            $contentInfo = $content->getVersionInfo()->getContentInfo();
            $tags = [
                    'content-' . $content->id,
                    'content-type-' . $contentInfo->contentTypeId,
            ];

            if ($contentInfo->mainLocationId) {
                $tags[] = 'location-' . $contentInfo->mainLocationId;
            }
        } else {
            $tags = [];
        }

        if ($view instanceof RelationView && ($relations = $view->getRelations())) {
            $tags += array_map(
                function (Relation $relation) {
                    return 'relation-' . $relation->getDestinationContentInfo()->id;
                },
                $relations
            );
        }

        return $tags;
    }
}
