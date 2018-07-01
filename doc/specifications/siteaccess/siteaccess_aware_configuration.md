# SiteAccess aware semantic configuration

## Description
Symfony Config component makes it possible to define *semantic configuration*, exposed to the end-developer. This
configuration is validated by rules you define, e.g. validating type (string, array, integer, boolean...).
Usually, once validated and processed this semantic configuration is then mapped to internal *key/value* parameters
stored in the `ServiceContainer`.

eZ Publish uses this for its core configuration, but adds another configuration level, the **SiteAccess**. For each
defined SiteAccess, we need to be able to use the same configuration tree in order to define SiteAccess specific config.
These settings then need to be mapped to SiteAccess aware internal parameters, that one can retrieve via the `ConfigResolver`.
For this, internal keys need to follow the format `<namespace>.<scope>.<parameter_name>`, `namespace` being specific
to your app/bundle, `scope` being the SiteAccess, SiteAccess group, `default` or `global`, `parameter_name` being
the actual setting *identifier*.

> For more information on ConfigResolver, namespaces and scopes, see [eZ Publish configuration basics](https://doc.ez.no/display/EZP/Configuration).

Goal of this feature is to make it easy to implement a *SiteAccess aware* semantic configuration and its mapping to
internal config for any eZ bundle developer.


## Semantic configuration parsing
An abstract `Configuration` class has been added, simplifying the way to add a SiteAccess settings tree like the following:

```yaml
acme_demo:
    system:
        my_siteaccess:
            hello: "world"
            foo_setting:
                an_integer: 456
                enabled: true

        my_siteaccess_group:
            hello: "universe"
            foo_setting:
                foo: "bar"
                some: "thing"
                an_integer: 123
                enabled: false
```

Class FQN is `eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration`.
All you have to do is to extend it and use `$this->generateScopeBaseNode()`:

```php
namespace Acme\DemoBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'acme_demo' );

        // $systemNode will then be the root of siteaccess aware settings.
        $systemNode = $this->generateScopeBaseNode( $rootNode );
        $systemNode
            ->scalarNode( 'hello' )->isRequired()->end()
            ->arrayNode( 'foo_setting' )
                ->children()
                    ->scalarNode( "foo" )->end()
                    ->scalarNode( "some" )->end()
                    ->integerNode( "an_integer" )->end()
                    ->booleanNode( "enabled" )->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
```

> Default name for the *SiteAccess root node* is `system`, but you can customize it.
> For this, just pass the name you want to use as a 2nd argument of `$this->generateScopeBaseNode()`.


## Mapping to internal settings
Semantic configuration must always be *mapped* to internal *key/value* settings within the `ServiceContainer`.
This is usually done in the DIC extension.

For SiteAccess aware settings, new `ConfigurationProcessor` and `Contextualizer` classes have been introduced to ease the process.

```php
namespace Acme\DemoBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AcmeDemoExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = $this->getConfiguration( $configs, $container );
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'default_settings.yml' );

        // "acme_demo" will be the namespace as used in ConfigResolver format.
        $processor = new ConfigurationProcessor( $container, 'acme_demo' );
        $processor->mapConfig(
            $config,
            // Any kind of callable can be used here.
            // It will be called for each declared scope/SiteAccess.
            // $scopeSettings is passed by reference, making it possible to alter it for usage after `$processor->mapConfig()` has run.
            function ( &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
            {
                // Will map "hello" setting to "acme_demo.<$currentScope>.hello" container parameter
                // It will then be possible to retrieve this parameter through ConfigResolver in the application code:
                // $helloSetting = $configResolver->getParameter( 'hello', 'acme_demo' );
                $contextualizer->setContextualParameter( 'hello', $currentScope, $scopeSettings['hello'] );
            }
        );

        // Now map "foo_setting" and ensure keys defined for "my_siteaccess" overrides the one for "my_siteaccess_group"
        // It is done outside the closure as it is needed only once.
        $contextualizer = $processor->getContextualizer();
        $contextualizer->mapConfigArray( 'foo_setting', $config );
    }
}
```

> **Important**:
> Always ensure you have defined and loaded default settings.

`default_settings.yml`
```yaml
parameters:
    acme_demo.default.hello: world
    acme_demo.default.foo_setting:
        foo: ~
        some: ~
        planets: [Earth]
        an_integer: 0
        enabled: false
        j_adore: les_sushis
```

> **Using a dedicated mapper object**
> Instead of passing a callable to `$processor->mapConfig()`, an instance of `eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationMapperInterface`
  can be passed, for example if you have a lot of configuration to map and don't want to *pollute* your DIC extension class.


> **Tip**: You can map *simple settings* by calling `$contextualizer->mapSetting()`, without having to call `$processor->mapConfig()`.
  This method can also be proxied from the `ConfigurationProcessor` directly, e.g. doing `$processor->mapSetting( 'hello', $config )`

### Merging hash values between scopes
When you define a hash as semantic config, you sometimes don't want the SiteAccess settings to replace the default or group
values, but *enrich* them by appending new entries.
This is made possible by using `$contextualizer->mapConfigArray()`, which needs to be called outside the closure (before or after),
in order to be called only once.

> **Tip**: `$contextualizer->mapConfigArray()` can be proxied from the `ConfigurationProcessor` directly.

Consider the following default config:

```yaml
parameters:
    acme_demo.default.foo_setting:
        foo: ~
        some: ~
        planets: [Earth]
        an_integer: 0
        enabled: false
        j_adore: les_sushis
```

And then this semantic config:

```yaml
acme_demo:
    system:
        sa_group:
            foo_setting:
                foo: bar
                some: thing
                an_integer: 123

        # Assuming "sa1" is part of "sa_group"
        sa1:
            foo_setting:
                an_integer: 456
                enabled: true
                j_adore: le_saucisson
```

What we want here, is that keys defined for `foo_setting` are merged between default/group/SiteAccess:

```yaml
parameters:
    acme_demo.sa1.foo_setting:
        foo: bar
        some: thing
        planets: [Earth]
        an_integer: 456
        enabled: true
        j_adore: le_saucisson
```

#### Merge from *second level*
In the example above, entries were merged in respect to the scope order of precedence. However, if we define the `planets`
key for `sa1`, it will completely override the default value since the merge process is done at only 1 level.

You can add another level by passing `ContextualizerInterface::MERGE_FROM_SECOND_LEVEL` as an option (3rd argument) to
`$contextualizer->mapConfigArray()`.

*Default config*:
```yaml
parameters:
    acme_demo.default.foo_setting:
        foo: ~
        some: ~
        planets: [Earth]
        an_integer: 0
        enabled: false
        j_adore: [les_sushis]
```

*Semantic config*:
```yaml
acme_demo:
    system:
        sa_group:
            foo_setting:
                foo: bar
                some: thing
                planets: [Mars, Venus]
                an_integer: 123

        # Assuming "sa1" is part of "sa_group"
        sa1:
            foo_setting:
                an_integer: 456
                enabled: true
                j_adore: [le_saucisson, la_truite_a_la_vapeur]
```

*Result using `ContextualizerInterface::MERGE_FROM_SECOND_LEVEL` option*:
```yaml
parameters:
    acme_demo.sa1.foo_setting:
        foo: bar
        some: thing
        planets: [Earth, Mars, Venus]
        an_integer: 456
        enabled: true
        j_adore: [les_suhis, le_saucisson, la_truite_a_la_vapeur]
```

> There is also another option, `ContextualizerInterface::UNIQUE`, to be used when you want to ensure your array
  setting has unique values. It will only work on normal arrays though, not hashes.

#### Using mapper objects
As specified above, `$contextualizer->mapConfigArray()` is not to be used within the *scope loop*, like for simple values.
When using a closure/callable, you usually call it before or after `$processor->mapConfig()`. For mapper objects,
a dedicated interface can be used: `HookableConfigurationMapperInterface`, which defines 2 methods: `preMap()` and `postMap()`.

#### Limitations
A few limitation exist with this scope hash merge:

* Semantic setting name and internal name will be the same (like `foo_setting` in the examples above).
* Applicable to 1st level semantic parameter only (i.e. settings right under the SiteAccess name).
* Merge is not recursive. Only 2nd level merge is possible by using `ContextualizerInterface::MERGE_FROM_SECOND_LEVEL` option.

## SiteAccess configuration filtering
Since kernel v6.9, the SiteAccess configuration can be customized by 3rd party bundles before
it gets processed. It can be used to declare custom siteaccesses, prevent some names from being used...

To do so, you need to:
- define a `SiteAccessConfigurationFilter` that implements `eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter`,
- add this filter to the CoreBundle's extension.

Example of a SiteAccessConfigurationFilter:
```php
namespace AppBundle\Platform;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessConfigurationFilter;

class MySiteAccessConfigurationFilter implements SiteAccessConfigurationFilter
{
    function filter(array $configuration)
    {
        $configuration['list'][] = 'foo';

        return $configuration;
    }
}
```

Registering the filter:
```
namespace AppBundle;

use AppBundle\Platform\MySiteAccessConfigurationFilter;

class AppBundle
{
    public function build(ContainerBuilder $container)
    {
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addSiteAccessConfigurationFilter(new MySiteAccessConfigurationFilter());
    }
}
```
