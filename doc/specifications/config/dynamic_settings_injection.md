# Dynamic settings injection

## Description
Before 5.4, if one wanted to implement a service needing siteaccess-aware settings (e.g. language settings),
they needed to inject the whole `ConfigResolver` (`ezpublish.config.resolver`) and get the needed settings from it.

This was not very convenient nor explicit.
Goal of this feature is to allow developers to inject these dynamic settings explicitly from their service definition (yml, xml, annotation...).

## Usage
Static container parameters follows the `%<parameter_name>%` syntax in Symfony.

Dynamic parameters have the following: `$<parameter_name>[, <namespace>[, <scope>]]$`

Default namespace being `ezsettings`, and default scope being current siteaccess.

For more information, see [ConfigResolver documentation](https://confluence.ez.no/display/EZP/Configuration#Configuration-DynamicconfigurationwiththeConfigResolver).

## Example
### Injecting an eZ parameter
Defining a simple service needing `languages` parameter (i.e. prioritized languages).

> Note: Internally, `languages` parameter is defined as `ezsettings.<siteaccess_name>.languages`,
> `ezsettings` being eZ internal *namespace*.

**Before**
```yaml
parameters:
    acme_test.my_service.class: Acme\TestBundle\MyServiceClass

services:
    acme_test.my_service:
        class: %acme_test.my_service.class%
        arguments: [@ezpublish.config.resolver]
```

```php
namespace Acme\TestBundle;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

class MyServiceClass
{
    /**
     * Prioritized languages
     *
     * @var array
     */
    private $languages;

    public function __construct( ConfigResolverInterface $configResolver )
    {
        $this->languages = $configResolver->getParameter( 'languages' );
    }
}
```

**After**
```yaml
parameters:
    acme_test.my_service.class: Acme\TestBundle\MyServiceClass

services:
    acme_test.my_service:
        class: %acme_test.my_service.class%
        arguments: ["$languages$"]
```

```php
namespace Acme\TestBundle;

class MyServiceClass
{
    /**
     * Prioritized languages
     *
     * @var array
     */
    private $languages;

    public function __construct( array $languages )
    {
        $this->languages = $languages;
    }
}
```

### Injecting 3rd party parameters

```yaml
parameters:
    acme_test.my_service.class: Acme\TestBundle\MyServiceClass

    # "acme" is our parameter namespace.
    # Null is the default value.
    acme.default.some_parameter: ~
    acme.ezdemo_site.some_parameter: foo
    acme.ezdemo_site_admin.some_parameter: bar

services:
    acme_test.my_service:
        class: %acme_test.my_service.class%
        # The following argument will automatically resolve to the right value, depending on the current SiteAccess.
        # We specify "acme" as the namespace we want to use for parameter resolving.
        arguments: ["$some_parameter;acme$"]
```

```php
namespace Acme\TestBundle;

class MyServiceClass
{
    private $myParameter;

    public function __construct( $myParameter )
    {
        // Will be "foo" for ezdemo_site, "bar" for ezdemo_site_admin, or null if another SiteAccess.
        $this->myParameter = $myParameter;
    }
}
```
