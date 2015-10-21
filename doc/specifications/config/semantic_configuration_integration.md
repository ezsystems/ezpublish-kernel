# Integrating custom semantic configuration

> Added in 6.0 / 2015.09.02

When developing an external bundle, one might want to define semantic configuration that integrates
with existing eZ Platform's semantic configuration. That means such configuration would need to be
available under provided `ezpublish` DIC extension from CoreBundle. Hence such configuration would
also be SiteAccess aware.

To facilitate the above, two methods are provided in CoreBundle's DIC extension.

```php
/**
 * Adds a new config parser to the internal collection.
 *
 * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface $configParser
 */
public function addConfigParser(ParserInterface $configParser);

/**
 * Adds new default settings to the internal collection.
 *
 * @param string $fileLocation
 * @param array $files
 */
public function addDefaultSettings($fileLocation, array $files)
```

These methods are intended to be called from bundle's `build()` method:

```php
namespace Acme\FooBundle\AcmeFooBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeFooBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addConfigParser(new MyConfigParser());
        $eZExtension->addDefaultSettings('/path/to/my/', array('default_settings.yml'));
    }
}
```
