# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Requirements for PHP has been increased to PHP 7.1 as it is by now supported by all platforms,
  and provide substantial improved experience developing and working  with PHP compared to PHP 5.

* Requirement for Symfony has been lifted to 3.3, and will be further lifted to 3.4LTS once that is out and all our own
  thrirdpart libraries are known to work correctly with it.

* SPI: eZ\Publish\SPI\Persistence\User::deletePolicy adds role id argument
  Id of Role is added on deletePolicy() to be able to properly clear cache
  for the affected role.

* "cache_pool" service is now a Symfony 3 Cache Pool instead of Stash. if you type hinted against PSR-6 you should be
  somewhat safe, but be on the lookout for nuances in behaviour. If you used Stash features like cache hierarchy,
  you'll need to adapt. Recommendation is to adapt to use Symfony Cache, but you can also setup and use Stash yourself.

* Identifiers and remoteIds can no longer contain `{}()/\@` characters as they are not supported in cache keys with
  Symfony cache.

* "cache_pool_name" siteaccess setting has been removed & replaced by "cache_service_name" as the semantic is different.
  The new setting should contain the full service name of a  symfony cache service, by default app_cache.app is used.

* The method `eZ\Publish\Core\FieldType\Time\Value::fromTimestamp` returns `Time\Value` without
  taking into account the timezone. The reason for this change is consistency with the behavior of
  `eZ\Publish\Core\FieldType\DateAndTime\Value::fromTimestamp`.

* The Twig block `eztime_field` of `eZ/Bundle/EzPublishCoreBundle/Resources/views/content_fields.html.twig` is rendered using UTC timezone to avoid server timezone-related issues.

## Deprecations

_7.0 is a major version, hence does not introduce deprecations but rather removes some previously deprecated features,
and in some cases changes features._


## Removed features

* eZ\Bundle\EzPublishCoreBundle\ApiLoader\LazyRepositoryFactory
  Has been deprecated for a while in favour of lazy services by Symfony.

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
