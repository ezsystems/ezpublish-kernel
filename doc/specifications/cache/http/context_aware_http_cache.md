# Context aware HTTP cache

> **Important**: As of v5.4 / v2014.11, usage of `FOSHttpCacheBundle has been introduced.
> Please [refer to dedicated specification](fos_http_cache.md).

## Description
Being based on Symfony 2, eZ Publish 5 uses HTTP cache from version 5.0.
However this cache management is only available for anonymous users due to HTTP restrictions.

It is of course possible to make HTTP cache vary thanks to the `Vary` response header, but this header can only
be based on one of the request headers (e.g. `Accept-Encoding`).
Thus, to make the cache vary on a specific context (e.g. a hash based on a user roles and limitations), this context must be present
in the original request.

## Credits
This feature is based on [Context aware HTTP caching post](http://asm89.github.io/2012/09/26/context-aware-http-caching.html)
by [asm89](https://github.com/asm89).

## Solution
As the response can vary on a request header, the base solution is to make the kernel do a sub-request in order to retrieve
the context (aka **user hash**). Once the *user hash* has been retrieved, it's injected in the original request in
the `X-User-Hash` custom header, making it possible to *vary* the HTTP response on this header:

```php
<?php
use Symfony\Component\HttpFoundation\Response;

// ...

// Inside a controller action
$response = new Response();
$response->setVary( 'X-User-Hash' );
```

This solution is implemented in Symfony reverse proxy (aka *HttpCache*) and is also accessible to dedicated reverse
proxies like Varnish.

### Workflow
1. Reverse proxy receives the HTTP request (without the user hash).
2. Reverse proxy does a sub-request (emulated in the case of *HttpCache*).

    Sub-request **must** have the following headers:
    * `X-HTTP-Override: AUTHENTICATE`
    * `Accept: application/vnd.ez.UserHash+text`
    * Original cookie (mainly to keep trace of the sessionId)

3. eZ Publish returns an HTTP response containing the user hash in `X-User-Hash` header.
4. Reverse proxy adds the `X-User-Hash` header to the original request.

### User hash generation
When user hash generation is requested, eZ Publish will create a **hashable User Identity** object.

One can add information to the Identity object making the resulted hash vary.
This can be done by registering **Identity definers**.

```yaml
services:
    my_identity_definer:
        class: %my_identity_definer.class%
        tags:
            - { name: ezpublish.identity_definer }
```

Class for this service **must** implement `eZ\Publish\SPI\User\IdentityAware` interface:

```php
<?php

namespace Acme\TestBundle\Identity;

use eZ\Publish\SPI\User\IdentityAware;
use eZ\Publish\SPI\User\Identity;

class MyDefiner implements IdentityAware
{
    public function setIdentity( Identity $identity )
    {
        // Here I can add information to $identity.
        // value MUST be scalar.
        $identity->setInformation( 'my_key', 'my_value' );
    }
}
```

> **Note on performance**: User hash is **not** generated for each `AUTHENTICATE` request.
> It's cached using `Cookie` header string as key. Hence each user has its own hash, generated once per session.

#### Securing hash generation request
By default, hash generation requests are granted for localhost (`127.0.0.1`, `::1`, `fe80::1`).

If you want to enlarge the scope (e.g. if your Varnish server is not running on the same machine), you can override
`canGenerateUserHash()` protected method in your main kernel class (mostly `EzPublishKernel`).


### Varnish
Described behavior comes out of the box with Symfony reverse proxy, but it's of course possible ot use Varnish to achieve
the same.

This can be done thanks to [Varnish Curl vmod](https://github.com/varnish/libvmod-curl).

```
import curl;

sub vcl_recv {
    # Do a standard lookup on assets
    # Note that file extension list below is not extensive, so consider completing it to fit your needs.
    if (req.request == "GET" && req.url ~ "\.(css|js|gif|jpe?g|bmp|png|tiff?|ico|img|tga|wmf|svg|swf|ico|mp3|mp4|m4a|ogg|mov|avi|wmv|zip|gz|pdf|ttf|eot|wof)$") {
        return (lookup);
    }

    if (req.request == "GET") {
        # Pre-authenticate request to get shared cache, even when authenticated
        if (req.http.Cookie !~ "is_logged_in=" ) {
            # User don't have "is_logged_in" cookie => Set a hardcoded anonymous hash
            set req.http.X-User-Hash = "38015b703d82206ebc01d17a39c727e5";
        } else {
            # User is authenticated => fetch user hash
            curl.header_add("X-HTTP-Override: AUTHENTICATE");
            curl.header_add("Accept: application/vnd.ez.UserHash+text");
            curl.header_add("Cookie: " + req.http.Cookie);
            # Customize with real backend host
            curl.get("http://ezpublish5.dev/");
            if (curl.status() == 200) {
                set req.http.X-User-Hash = curl.header("X-User-Hash");
            }
        }
    }

    # If it passes all these tests, do a lookup anyway;
    return (lookup);
}
```
