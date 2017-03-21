# FOSHttpCacheBundle usage in eZ

As of v5.4 / v2014.11, usage of [FOSHttpCacheBundle](http://foshttpcachebundle.readthedocs.org/) has been introduced,
impacting the following features:

* Http cache purge
* User context hash

## Http cache clear
Varnish proxy client from FOSHttpCache lib is now used for clearing eZ Http cache, even when using Symfony HttpCache.
A single `BAN` request is sent to registered purge servers, containing a `X-Location-Id` header.
This header contains all Location IDs for which objects in cache need to be cleared.

### Symfony reverse proxy
Symfony reverse proxy (aka HttpCache) is supported out of the box, all you have to do is to activate it.

### Varnish
For cache clearing to work properly, following VCL code need to be used:

```
# Varnish 3 style

# Our Backend - We assume that eZ Publish Web server listen on port 80
backend ezpublish {
    .host = "ezpublish5.dev";
    .port = "80";
}

# ACL for purgers IP
acl purgers {
    "127.0.0.1";
    "192.168.0.0"/16;
}

# ACL for debuggers IP
acl debuggers {
    "127.0.0.1";
    "192.168.0.0"/16;
}

# Called at the beginning of a request, after the complete request has been received
sub vcl_recv {

    # Set the backend
    set req.backend = ezpublish;

    # Advertise Symfony for ESI support
    set req.http.Surrogate-Capability = "abc=ESI/1.0";

    # Add a unique header containing the client address (only for master request)
    # Please note that /_fragment URI can change in Symfony configuration
    if (!req.url ~ "^/_fragment") {
        if (req.http.x-forwarded-for) {
            set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
        } else {
            set req.http.X-Forwarded-For = client.ip;
        }
    }

    # Trigger cache purge if needed
    call ez_purge;

    # ...

    # If it passes all these tests, do a lookup anyway;
    return (lookup);
}

# Called when the requested object has been retrieved from the backend
sub vcl_fetch {

    # ...

    # Optimize to only parse the Response contents from Symfony
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    return (deliver);
}

# Handle purge
# You may add FOSHttpCacheBundle tagging rules
# See http://foshttpcache.readthedocs.org/en/latest/varnish-configuration.html#id4
sub ez_purge {

    if (req.request == "PURGE" || req.request == "BAN") {
        if (!client.ip ~ purgers) {
            error 405 "Method not allowed";
        }

        if (req.http.X-Location-Id) {
            ban( "obj.http.X-Location-Id ~ " + req.http.X-Location-Id );
            if (client.ip ~ debuggers ) {
                set req.http.X-Debug = "Purge all locations send via backend";
            }
            error 200 "Purge of content connected to the location id(" + req.http.X-Location-Id + ") done.";
        }
    }
}
```

## User context hash
[FOSHttpCacheBundle *User Context feature* is used](http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html)
is activated by default.

As the response can vary on a request header, the base solution is to make the kernel do a sub-request in order to retrieve
the context (aka **user context hash**). Once the *user hash* has been retrieved, it's injected in the original request in
the `X-User-Hash` header, making it possible to *vary* the HTTP response on this header:

> Name of the [user hash header is configurable in FOSHttpCacheBundle](http://foshttpcachebundle.readthedocs.org/en/latest/reference/configuration/user-context.html). 
> By default eZ Publish sets it to `**X-User-Hash**`.

```php
<?php
use Symfony\Component\HttpFoundation\Response;

// ...

// Inside a controller action
$response = new Response();
$response->setVary( 'X-User-Hash' );
```

This solution is [implemented in Symfony reverse proxy (aka *HttpCache*)](http://foshttpcachebundle.readthedocs.org/en/latest/features/symfony-http-cache.html) 
and is also accessible to [dedicated reverse proxies like Varnish](http://foshttpcache.readthedocs.org/en/latest/varnish-configuration.html).
 

### Workflow
Please refer to [FOSHttpCacheBundle documentation on how user context feature works](http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html#how-it-works).

### User hash generation
Please refer to [FOSHttpCacheBundle documentation on how user hashes are being generated](http://foshttpcachebundle.readthedocs.org/en/latest/features/user-context.html#generating-hashes).

eZ Publish already interferes in the hash generation process, by adding current user permissions and limitations.
One can also interfere in this process by [implementing custom context provider(s)](http://foshttpcachebundle.readthedocs.org/en/latest/reference/configuration/user-context.html#custom-context-providers).


### Varnish
Described behavior comes out of the box with Symfony reverse proxy, but it's of course possible ot use Varnish to achieve
the same.

```
# Varnish 3 style
# Our Backend - We assume that eZ Publish Web server listen on port 80
backend ezpublish {
    .host = "ezpublish5.dev";
    .port = "80";
}

# Called at the beginning of a request, after the complete request has been received
sub vcl_recv {

    # Set the backend
    set req.backend = ezpublish;

    # ...

    # Retrieve client user hash and add it to the forwarded request.
    call ez_user_hash;

    # If it passes all these tests, do a lookup anyway;
    return (lookup);
}

# Sub-routine to get client user hash, for context-aware HTTP cache.
# Don't forget to correctly set the backend host for the Curl sub-request.
sub ez_user_hash {

    # Prevent tampering attacks on the hash mechanism
    if (req.restarts == 0
        && (req.http.accept ~ "application/vnd.fos.user-context-hash"
            || req.http.x-user-context-hash
        )
    ) {
        error 400;
    }

    if (req.restarts == 0 && (req.request == "GET" || req.request == "HEAD")) {
        # Anonymous user => Set a hardcoded anonymous hash
        if (req.http.Cookie !~ "eZSESSID" && !req.http.authorization) {
            set req.http.X-User-Hash = "38015b703d82206ebc01d17a39c727e5";
        }
        # Pre-authenticate request to get shared cache, even when authenticated
        else {
            set req.http.x-fos-original-url    = req.url;
            set req.http.x-fos-original-accept = req.http.accept;
            set req.http.x-fos-original-cookie = req.http.cookie;
            # Clean up cookie for the hash request to only keep session cookie, as hash cache will vary on cookie.
            set req.http.cookie = ";" + req.http.cookie;
            set req.http.cookie = regsuball(req.http.cookie, "; +", ";");
            set req.http.cookie = regsuball(req.http.cookie, ";(eZSESSID[^=]*)=", "; \1=");
            set req.http.cookie = regsuball(req.http.cookie, ";[^ ][^;]*", "");
            set req.http.cookie = regsuball(req.http.cookie, "^[; ]+|[; ]+$", "");

            set req.http.accept = "application/vnd.fos.user-context-hash";
            set req.url = "/_fos_user_context_hash";

            # Force the lookup, the backend must tell not to cache or vary on all
            # headers that are used to build the hash.

            return (lookup);
        }
    }

    # Rebuild the original request which now has the hash.
    if (req.restarts > 0
        && req.http.accept == "application/vnd.fos.user-context-hash"
    ) {
        set req.url         = req.http.x-fos-original-url;
        set req.http.accept = req.http.x-fos-original-accept;
        set req.http.cookie = req.http.x-fos-original-cookie;

        unset req.http.x-fos-original-url;
        unset req.http.x-fos-original-accept;
        unset req.http.x-fos-original-cookie;

        # Force the lookup, the backend must tell not to cache or vary on the
        # user hash to properly separate cached data.

        return (lookup);
    }
}

sub vcl_fetch {

    # ...
    
    if (req.restarts == 0
        && req.http.accept ~ "application/vnd.fos.user-context-hash"
        && beresp.status >= 500
    ) {
        error 503 "Hash error";
    }
}

sub vcl_deliver {
    # On receiving the hash response, copy the hash header to the original
    # request and restart.
    if (req.restarts == 0
        && resp.http.content-type ~ "application/vnd.fos.user-context-hash"
        && resp.status == 200
    ) {
        set req.http.x-user-hash = resp.http.x-user-hash;

        return (restart);
    }

    # If we get here, this is a real response that gets sent to the client.

    # Remove the vary on context user hash, this is nothing public. Keep all
    # other vary headers.
    set resp.http.Vary = regsub(resp.http.Vary, "(?i),? *x-user-hash *", "");
    set resp.http.Vary = regsub(resp.http.Vary, "^, *", "");
    if (resp.http.Vary == "") {
        remove resp.http.Vary;
    }

    # Sanity check to prevent ever exposing the hash to a client.
    remove resp.http.x-user-hash;
}

```

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
