# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes


## Deprecations



## Removed features


* HttpCache and Kernel removed
  As previously deprecated in 6.11, these classes are now removed:
  eZ\Bundle\EzPublishCoreBundle\HttpCache
  eZ\Bundle\EzPublishCoreBundle\Kernel


* HttpCache features has been removed in favour of ezplatform-http-cache package, affects:
  - Classes
    - eZ\Bundle\EzPublishCoreBundle\Cache\Http\InstantCachePurge
    - eZ\Bundle\EzPublishCoreBundle\Cache\Http\VarnishProxyClientFactory
    - eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\HttpCachePass
    - eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\ContentPurger
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener\*
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\FOSPurgeClient
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\RequestAwarePurger
    - eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot\*
    - eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface
  - Services
    - ezpublish.http_cache.purger.instant
    - ezpublish.http_cache.purge
    - ezpublish.http_cache.purge_client.local
    - ezpublish.http_cache.store
    - ezpublish.http_cache.cache_manager
    - ezpublish.http_cache.proxy_client.varnish.factory
    - ezpublish.http_cache.purge_client.fos
    - ezpublish.http_cache.purge_client
    - ezpublish.cache_clear.content.base_locations_listener
    - ezpublish.cache_clear.content.parent_locations_listener
    - ezpublish.cache_clear.content.related_locations_listener
    - ezpublish.http_cache.signalslot.*

 * User identify / hash generation duplicated by FosHttpCache features
  - Classes
    - eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator
    - eZ\Publish\Core\MVC\Symfony\Security\User\Identity
  - Services
    - ezpublish.user.identity
    - ezpublish.user.hash_generator
