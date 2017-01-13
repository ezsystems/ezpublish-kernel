# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Requirements for PHP has been increased to PHP 7.x as it is by now supported by all platforms,
  and provide substantial improved experience developing and working  with PHP compared to PHP 5.

* Requirement for Symfony has been lifted to 3.2, and will be further lifted to 3.4LTS once that is out and all our own
  thrirdpart libraries are known to work correctly with it.

* SPI: eZ\Publish\SPI\Persistence\User::deletePolicy adds role id argument
  Id of Role is added on deletePolicy() to be able to properly clear cache
  for the affected role.

* "cache_pool" service is now a Symfony 3 Cache Pool instead of Stash. if you type hinted against PSR-6 you should be
  somewhat safe, but be on the lookout for nuances in behaviour. If you used Stash features like cache hierarchy,
  you'll need to adapt. Recommendation is to adapt to use Symfony Cache, but you can also setup and use Stash yourself.

* "cache_pool" service is now a Symfony 3 Cache Pool instead of Stash. if you type hinted against PSR-6 you should be
  somewhat safe, but be on the lookout for nuances in behaviour. If you used Stash features like cache hierarchy,
  you'll need to adapt. Recommendation is to adapt to use Symfony Cache, but you can also setup and use Stash yourself.

* "cache_pool_name" siteaccess setting has been removed & replaced by "cache_service_name" as the semantic is different.
  The new setting should contain the full service name of a  symfony cache service, by default app_cache.app is used.


## Deprecations

_7.0 is a major version, hence does not introduce deprecations but rather removes some previously deprecated features,
and in some cases changes features._


## Removed features
