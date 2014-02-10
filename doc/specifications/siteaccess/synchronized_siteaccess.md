# Synchronized SiteAccess

## Description
Sometimes, when using services that depends on `ezpublish.siteaccess` service, you'll find that the injected `SiteAccess`
object doesn't correspond to what it is supposed to match. Reason is that SiteAccess matching is done in a `kernel.request`
even listener and before it's done, a *generic* SiteAccess is being provided, with `default` as name.

This issue can occur when SiteAccess dependent services are being constructed *before* SiteAccess matching process.
This is particularly the case when one adds a `kernel.request` listener as all listeners (and their dependencies) are being
instantiated by the event dispatcher *before* the event is sent.

## Solution
To solve this issue, and provide more stability and maintainability to 3rd party code, `ezpublish.siteaccess` *service*
has been declared as a **synchronized service**. It means that each time it is injected in the ServiceContainer, it will
be re-injected in all services that depends on it.

[More information on synchronized services can be found in Symfony documentation](http://symfony.com/doc/2.3/cookbook/service_container/scopes.html#using-synchronized-service].

## Usage
In order to take advantage of synchronized SiteAccess injection, you need to use a method call in your service definition.
Additionally, you can use `eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware` interface.

As an example, let's define a simple service which depends on the repository's ContentService and the current SiteAccess.
In that case, SiteAccess object will be injected whenever it changes, via `setSiteAccess()` setter method.

```yaml
parameters:
    acme.test.my_service.class: Acme\AcmeTestBundle\MyService

services:
    acme.test.my_service:
        class: %acme.test.my_service.class%
        arguments: [@ezpublish.api.service.content]
        calls:
            - [setSiteAccess, [@ezpublish.siteaccess]]
```

```php
<?php
namespace Acme\AcmeTestBundle;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;

class MyService implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    public function __construct( ContentService $contentService )
    {
        $this->contentService = $contentService;
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->siteAccess = $siteAccess;
    }
}
```
