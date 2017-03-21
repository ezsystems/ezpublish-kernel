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
- `content-<content-id>` :
  _Meta tag used on anything that are affected on changes to content, on content itself as well as location and so on._

- `content-fields-<content-id>` :
  _Used on content/user object itself (with fields), for use when operations affects field values but not content meta info._

- `content-fields-type-<content-type-id>` :
  _Same as above, but specifically for use when content type changes affecting content fields of it's type._

- `location-<location-id>` :
  _Meta tag used on anything that are affected on changes to location, needs to be invalidated if location changes._

- `location-path-<location-id>` :
  _Same as above, additional tags for all parents, for operations that changes the tree itself, like move/remove/(..)._

- `location-data-<location-id>` :
  _Used on location, and invalidated when operations affect the properties on location only, e.g. swap/update._

- `location-path-data-<location-id>` :
  _Same as above, but for operations affecting data in the tree, e.g. hide/unhide._

- `language-<language-id>` :
  _Used on languages, and invalidated when operations affect a language._

- `type-<type-id>` :
  _Used on types, and invalidated when operations affect a type._

- `type-group-<type-group-id>` :
  _Used on type groups, and invalidated when operations affect a type groups._

- `type-map` :
  _Used on type map info, like calculated info on searchable fields, invalidated on type changes._

- `state-<type-id>` :
  _Used on states, and invalidated when operations affect a state._

- `state-group-<state-group-id>` :
  _Used on state groups, and invalidated when operations affect a state groups._

- `section-<section-id>` :
  _Used on sections, and invalidated when operations affect a section._

- `urlAlias-location-<location-id>` :
  _Used on url alias, invalidated on location operations affecting url alias._

- `urlAlias-notFound` :
  _Used for not found lookups for url alias by url as this is hot spot, invalidated on urlAlias creation._

- `role-<role-id>` :
  _Used on roles, and invalidated when operations affect a role (role and policy operations)._

- `policy-<policy-id>` :
  _Used on policies, and invalidated when operations affect a policy._

- `role-assignment-<role-assignment-id>` :
  _Used on role assignment, and invalidated when operations affect a role assignment._

- `role-assignment-group-list-<content-id>` :
  _Used for list of role assignment, and invalidated when operations affect it from content side._

- `role-assignment-role-list-<content-id>` :
  _Used for list of role assignment, and invalidated when operations affect it from role side._


### Example of internal use


On `LocationHandler->create()` the following invalidation is done:

```php
    $this->cache->invalidateTags(['content-' . $locationStruct->contentId, 'role-assignment-group-list-' . $locationStruct->contentId]);
```

This is done since the operation might affect content if main location changes, and as it might affect roles assignments
on the given that are connected to the given content (as in user might get additional roles when new location is added for user).
