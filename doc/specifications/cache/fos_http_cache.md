# FOSHttpCacheBundle usage in eZ

Introduced with eZ Publish 5.4, [FOSHttpCacheBundle][fos] is supported by eZ Platform, and covers the following
features:

* Tags purging/baning (since version 1.8)
* Http cache purge/ban
* User context hash

## Http cache clear
Varnish proxy client from FOSHttpCache lib is now used for clearing eZ Http cache, even when using Symfony HttpCache.
It sends, for each cache tag that needs to be expired, a `PURGE` request with a `key` header to the registered purge servers.

### Symfony reverse proxy
Symfony reverse proxy (aka HttpCache) is supported out of the box, all you have to do is to activate it.

### Varnish
For cache clearing to work properly, you can use the VCL from the [ezplatform `doc/varnish` directory][varnish_doc].

## User context hash
[FOSHttpCacheBundle *User Context feature* is used][fos_user_context] is activated by default.

As the response can vary on a request header, the base solution is to make the kernel do a sub-request in order to retrieve
the context (the **user context hash**). Once the *user hash* has been retrieved, it is injected into the original request 
as the `X-User-Hash` header, making it possible to *vary* the HTTP response on this header:

> The name of the [user hash header is configurable in FOSHttpCacheBundle][fos_user_context]. 
> By default eZ Publish sets it to `**X-User-Hash**`.

```php
<?php
use Symfony\Component\HttpFoundation\Response;

// ...

// Inside a controller action
$response = new Response();
$response->setVary( 'X-User-Hash' );
```

This solution is [implemented in Symfony reverse proxy (aka *HttpCache*)][fos_symfony_cache] 
and is also accessible to [dedicated reverse proxies like Varnish][fos_varnish_cache].
 

### Workflow
Please refer to [FOSHttpCacheBundle documentation on how user context feature works][fos_user_context#how].

### User hash generation
Please refer to [FOSHttpCacheBundle documentation on how user hashes are being generated][fos_user_context#hashes].

eZ Platform already interferes in the hash generation process, by adding current user permissions and limitations.
One can also interfere in this process by [implementing custom context provider(s)][fos_user_context#providers].


### Varnish
While the described behavior comes out of the box with Symfony reverse proxy, Varnish is also supported. The documented
[eZ Platform VCL][_doc].

### Default options for FOSHttpCacheBundle defined in eZ
The following configuration is defined in eZ by default for FOSHttpCacheBundle.
You may override these settings.

```yaml
fos_http_cache:
    proxy_client:
        # "varnish" is used, even when using Symfony HttpCache.
        default: varnish
        varnish:
            # Means http_cache.purge_servers defined for current SiteAccess.
            servers: [$http_cache.purge_servers$]
            
    user_context:
        enabled: true
        # User context hash is cached during 10min
        hash_cache_ttl: 600
        user_hash_header: X-User-Hash
```

[varnish_doc]: https://github.com/ezsystems/ezplatform/blob/master/doc/varnish
[fos]: http://foshttpcachebundle.readthedocs.org/
[fos_user_context]: http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html
[fos_user_context#how]: http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html#how_it_works
[fos_user_context#providers]: http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html#custom-context-providers
[fos_user_context_hashes]: http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html#generating-hashes
[fos_symfony_cache]: http://foshttpcachebundle.readthedocs.org/en/latest/features/symfony-http-cache.html
[fos_varnish_cache]: http://foshttpcache.readthedocs.org/en/latest/varnish-configuration.html
