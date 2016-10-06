# Multi Tagging


Cache tagging, aka cache labeling is a concepts being introduced in both:
- HttpCache using both the builtin enhanced PHP based Symfony Proxy, and Varnish *(with plain BAN and with xkey vmod)*
- Repository Cache *(coming, to replace current persistence cache)*

This document concentrates on defining out of the box tags, and attempts to give guidelines for how to
use them both in responses and how to best handle invalidation.

## Tags

As the systems is extensible, important part of this is to document the used tags to avoid wrong use, or conflicts.
If you add own tag types, please prefix with 1-3 letters abbreviating your *(company/full)* name.

The tags needs to be made in a way so that cache can be cleared using nothing but what info is available on signal,
and the signals will need to be expanded to contain the relevant info depending on operation they correspond to.

#### Content Tags


Tags applied to Content View and Content REST Object:

- `content-<content-id>`

    *Tagging*: Used for tagging content responses with id.
    
    *Clearing*: When a operation is specifically affecting just given content.

- `content-type-<content-type-id>`

    *Tagging*: Used for tagging content responses with type id.
    
    *Clearing*: When a operation is specifically affecting content type, typically soft purge all affected content.

#### Location and Content Location Tags


- `location-<location-id>`

    If content has locations we need additional tags on the content response to be able to clear on operations affecting a
    given location or a tree.
    
    *Tagging*: Used for tagging content responses with all it's locations.
    
    *Clearing*: When a operation is specifically affecting one or several locations, on tree operations `path` is more relevant.


- `parent-<parent-location-id>`

    *Tagging*: Used for tagging content responses with all it's direct parents.
    
    *Clearing*: When a operation is specifically affecting parent location(s), on tree operations `path` is more relevant.

- `path-<path-location-id>`

##### Relations



- `relation-<relation-content-id>`

*Tagging*: Used for tagging content responses with all it's relations, where content id is the id of the other side of relation.
*Clearing*: When operations also affect reverse relations we can clear them using content id of self.

_Note: These tags are mainly relevant for field (field, embed, link) relations as change like deletion of realtion has an
effect on the output of the given content (it should not render links to the given relation anymore)._



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



