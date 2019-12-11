# CORE DEV DOC: Persistence\Cache

SPI Persistence Cache is a layer aiming to cache calls to Persistence (backend database) deemed costly under load. It
tries to balance this need up against the complexity and additional [system overhead](#guidelines-for-core-development) imposed by caching.

## Design

Handlers using `AbstractHandler` internally consist of one cache layer:

- "Shared cache": A Symfony Cache based cache pool, supporting a range of cache adapters like filesystem, Redis, Memcached.
   Note: Due to being shared by design, clusters need remote cache, thus multi lookups are strongly advised to reduce round trip latency.

Handlers using `AbstractInMemoryHandler` / `AbstractInMemoryPersistenceHandler` in addition add a second cache layer in front of "Shared cache":

- "InMemory cache": A burst cache in PHP aiming at covering the most heavily used parts of the Content model to reduce repeated lookups to remote cache system.
   Note: It's not shared but per request/process. To keep risk of race condition negligible, it has own milliseconds ttl & item limits.

There are abstract test classes for the respective abstract classes above, these are opinionated and enforce conventions to:
- Avoid too much logic in cache logic _(e.g. warm-up logic)_, which can be a source of bugs.
- Avoids having to write error-prone test cases for every method.

_This ensures the cache layer is far less complex to maintain and evolve than what was the case in 1.x._


### Tags

List of content tags that can be somewhat safely "semi-officially" used to clear related entities in cache:
- `content-<id>`: Cache tag which refers to Content/ContentInfo entity.
- `location-<id>`: Cache tag which refers to Locations and/or their assigned Content/ContentInfo entities.
- `location-path-<id>`: Like the tag above but applied to all Content/Locations in the subtree of this ID, so it can be used by tree operations.
- `content-fields-type-<type-id>`: Cache tag which refers to entries containing Field data. It's used on Content Type changes that affect all Content items of the type.

_For further tags used for other internal use cases, see the *Handlers for how they are used._


## Guidelines for core development

### Shared Cache: When to use, when not to use

It's worth noting that shared cache comes at a cost:
- Increased complexity
- Latency per round trip
- Memory use

Because of that, *typically* avoid introducing cache if:
- Lookup is per user => _it will consume a lot of memory and have very low hit ratio_
- For drafts => _usually belongs to a single user and is short-lived_
- Infrequently used lookups
- Lookups that are fast against DB even under load _(see also notes in "Possible future plans")_


### Tags: When to use, when not to use

Like cache, tags also comes at a cost:
- Slower invalidation
- Memory cost
  - _E.g.: ATM on RedisTagAwareAdapter tag relation data is even non-expiring as it needs to guarantee surviving cache._

For those reasons, only introduce use a tag:
- Mainly to represent an entity _(e.g. `content-<id>`)_
- Only if it's represented on many different cache keys or if a key can have a lot of different variants.
    - _Tip: Otherwise prefer to delete by cache key(s) when cache clear is needed, it will be faster and consume less memory._

### Possible future considerations

Ideally and for best effect, the cache should be in place to cache results of complex calculations based on multiple backend
lookups. Caching lightweight SPI lookups sometimes might not provide benefits, while still consume Redis/Memcached memory.

This is why it's been discussed to move some of the caching to API instead in a future major release.
However, that would require that permissions are split into their own Repo layer => In order to not end
up having to cache per user => Which would result in waste of memory and low cache hit ratio.
It would also need to deal with serialization of API value objects, which are more complex (lazy properties, xmldocument, ...).
