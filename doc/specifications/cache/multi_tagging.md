# Multi-tagging

Cache tagging, aka cache labeling is a concepts being introduced in both:
- HttpCache using both the builtin enhanced PHP based Symfony Proxy, and Varnish *(with plain BAN and with xkey vmod)*
- Repository Cache *(coming, to replace current persistence cache)*

This document concentrates on defining out of the box tags, and attempts to give guidelines for how to
use them both in responses and how to best handle invalidation.

## Recommendations

### Avoiding Cache Stamping effect

_Cache stamped (dog-piling): That several requests are requesting a cache item at the same time, worst case being a high
traffic page like front page being expired and all traffic is in parallel trying to regenerate the cache overloading the
system._

Two ways to avoid:


### Staleness / Soft purge vs instant UI updates

For HttpCache, to be able to archive stable performance of your setup our recommendation involves using Varnish with
xkey vmod, and soft purge of http cache. This means the given cache item will be re-generated on next request, and until
cache has been refreshed all other parallel requests are served stale cache item *(the old version)*.

This is great for avoiding cache stamped effect, however for things like the Platform UI you'll need to take this into
account. There are two ways of solving this:
- Make UI code aware that operations won't update the response immediately, like in distributed systems with eventually consistency.
- Setup UI on separate admin/backend domain where cache is shared with main domain, but where grace time is always 0.

### Cache stamped protection

Normally archived by having a percentage of randomness in expiry before the actual expiry time, so not several requests
end up trying to re generate a given cache, or several caches in parallel typically at the same time.

### Avoiding to eager tag invalidation



