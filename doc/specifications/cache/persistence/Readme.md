# Persistence Cache

Persistence cache refers to a decorator implementation of `eZ\Publish\SPI\Persistence` that only caches data to reduce
lockups to underlying persisted storage engine. Implementation can be found in `eZ\Publish\Core\Persistence\Cache`.

As of kernel 7.0 the cache pool used internally is Symfony cache. And for the most part we use it as an implementation
of PSR-6. However as we have more advance needs for cache invalidation we specify that we require implementation of
`Symfony\Component\Cache\Adapter\TagAwareAdapterInterface`, as we take advantage of Cache tagging.

Tags are set when a cache item is saved, and used by any code that needs to invalidate a specif part of the cache.
You can think of it as a additional index for the cache item that you may invalidate it by, allowing you to index
the cache in a ways so exact items are invalidated on specific bulk operations affecting a sub set of the cache pool.

### Tag usage in Persistence Cache

List of tags and their meaning:
- `c-<content-id>` :
  _Meta tag used on anything that are affected on changes to content, on content itself as well as location and so on._

- `c-<content-id>-v-<version-number>`
   _Used for specific versions, usuefull for clearing cache of a specific draft on changes for instance._

- `cft-<content-type-id>` :
  _Used on content/user object itself (with fields), for use when operations affect content type or its content fields._

- `l-<location-id>` :
  _Meta tag used on anything that are affected on changes to location, needs to be invalidated if location changes._

- `lp-<location-id>` :
  _Same as above, additional tags for all parents, for operations that changes the tree itself, like move/remove/(..)._

- `la-<language-id>` :
  _Used on languages, and invalidated when operations affect a language._

- `t-<type-id>` :
  _Used on types, and invalidated when operations affect a type._

- `tg-<type-group-id>` :
  _Used on type groups, and invalidated when operations affect a type groups._

- `tm` :
  _Used on type map info, like calculated info on searchable fields, invalidated on type changes._

- `s-<type-id>` :
  _Used on states, and invalidated when operations affect a state._

- `sg-<state-group-id>` :
  _Used on state groups, and invalidated when operations affect a state groups._

- `se-<section-id>` :
  _Used on sections, and invalidated when operations affect a section._

- `urlal-<location-id>` :
  _Used on url alias, invalidated on location operations affecting url alias._

- `urlanf` :
  _Used for not found lookups for url alias by url as this is hot spot, invalidated on urlAlias creation._

- `r-<role-id>` :
  _Used on roles, and invalidated when operations affect a role (role and policy operations)._

- `p-<policy-id>` :
  _Used on policies, and invalidated when operations affect a policy._

- `ra-<role-assignment-id>` :
  _Used on role assignment, and invalidated when operations affect a role assignment._

- `ragl-<content-id>` :
  _Used for list of role assignment, and invalidated when operations affect it from content side._

- `rarl-<content-id>` :
  _Used for list of role assignment, and invalidated when operations affect it from role side._


### Example of internal use


On `LocationHandler->create()` the following invalidation is done:

```php
    $this->cache->invalidateTags(['c-' . $locationStruct->contentId, 'ragl-' . $locationStruct->contentId]);
```

This is done since the operation might affect content if main location changes, and as it might affect roles assignments
on the given that are connected to the given content (as in user might get additional roles when new location is added for user).
