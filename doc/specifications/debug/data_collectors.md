# eZ data collectors

Symfony profiler let any bundle register *data collectors* and displayed collected data in the web profiler toolbar
and debug panel. eZ has its own data collector.

## Extensibility
As of v6.0, it is possible to display custom collected data under eZ toolbar / panel.
The main data collector has been splitted into several dedicated ones and now only aggregates data collected by
registered sub-collectors.

## Native data collectors
Following data collectors are part of `EzPublishDebugBundle`:

* `PersistenceCacheCollector`: Collects information on persistence cache (aka SPI cache) efficiency (hits/miss).
* `TemplatesDataCollector`: Collects information on loaded templates.

## Custom data collector
For data to appear under eZ toolbar / panel, it is only needed to write a service implementing
`Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface`, or simply extending `Symfony\Component\HttpKernel\DataCollector\DataCollector`.

This service, instead of being tagged as `data_collector`, needs to be tagged as `ezpublish_data_collector`.
This service tag takes 2 additional arguments indicating which template(s) to use for displaying collected data in the
toolbar and/or panel. Those templates will be *included* by the main eZ data collector, exposing your collector object,
like for any regular data collector.

### Example
```yml
parameters:
    ezpublish_debug.persistence_collector.class: eZ\Bundle\EzPublishDebugBundle\Collector\PersistenceCacheCollector
    ezpublish_debug.templates_collector.class: eZ\Bundle\EzPublishDebugBundle\Collector\TemplatesDataCollector

services:
    ezpublish_debug.persistence_collector:
        class: %ezpublish_debug.persistence_collector.class%
        arguments: [@ezpublish.spi.persistence.cache.persistenceLogger]
        tags:
            -
                name: ezpublish_data_collector
                id: "ezpublish.debug.persistence"
                panelTemplate: "EzPublishDebugBundle:Profiler/persistence:panel.html.twig"
                toolbarTemplate: "EzPublishDebugBundle:Profiler/persistence:toolbar.html.twig"

    ezpublish_debug.templates_collector:
        class: %ezpublish_debug.templates_collector.class%
        tags:
            -
                name: ezpublish_data_collector
                panelTemplate: "EzPublishDebugBundle:Profiler/templates:panel.html.twig"
                toolbarTemplate: "EzPublishDebugBundle:Profiler/templates:toolbar.html.twig"

```

For further example, refer to [`PersistenceCacheCollector`](https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Bundle/EzPublishDebugBundle/Collector/PersistenceCacheCollector.php) 
or [`TemplatesDataCollector`](https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Bundle/EzPublishDebugBundle/Collector/TemplatesDataCollector.php) implementation.
