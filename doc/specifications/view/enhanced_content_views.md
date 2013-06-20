# Enhanced views for Content/Location

In many cases, the default variables that are injected in the template to display a content/location is not sufficient
and will lead you to do many sub-requests in order to access different parameters.

Typical use cases are access to:

* Settings (either coming from ConfigResolver or ServiceContainer)
* Current content's ContentType object
* Current location's parent
* Current's location children count
* Main location and alternative locations for the current content
* etc…

Furthermore, in advanced cases you may want to use your own controller to display the current content/location instead of
using the built-in `ViewController`.

## Description
This feature covers 3 general use cases:

* Adds a [generic listener to `ezpublish.pre_content_view` event](https://confluence.ez.no/display/EZP/Parameters+injection+in+content+views), bound to the template selection rules, so that you can inject configured parameters in the selected view.
* Lets you configure a custom controller with the configured matcher rules.
* Lets you override the built-in view controller in a clean way.

## Parameters injection in view templates
This functionality is meant for simple to intermediate needs.
The goal is to expose additional variables in your view templates from the template selection rules configuration.

You can inject several types of parameters:

* Plain parameters, which values are directly defined in the configuration (including arrays, hashes, booleans…)
* Parameter references from the ServiceContainer (e.g. `%my.parameter%`)
* [Parameter references from ConfigResolver] (aka *siteaccess aware parameters*)
* Services that act as *parameter providers*

See [full example](#full-example) for practical details.

### Parameter references from ConfigResolver
In order to get *siteaccess aware parameters*, you usually need to use [the ConfigResolver](https://confluence.ez.no/display/EZP/Configuration#Configuration-DynamicconfigurationwiththeConfigResolver).

To inject this kind of parameters into your view template via the configuration, a new configuration syntax is introduced:
`$<paramName>[;<namespace>[;<scope>]]$`.

Full syntax would be: `$my_setting;custom_namespace;ezdemo_site_admin$`, which is an equivalent for `$configResolver->getParameter( 'my_setting', 'custom_namespace', 'ezdemo_site_admin );` in PHP.

### Parameter provider services
In some cases settings are not sufficient (i.e. if you need to always have the current ContentType available, or the current children count).

Services cannot be directly injected. but you can define *view parameters provider* services, 
meaning that they will provide the variables to inject in the view template.
Moreover, variables returned by those services will be *namespaced* by the parameter name provided in the configuration.

Parameter provider services must implement `eZ\Bundle\EzPublishCoreBundle\Templating\ViewParameterProvider` interface,
unless you provide a `method` in the template selection configuration.

See the [full example below](#full-example), for details.


## Full example
This feature would allow to configure a content/location/block view the following way:

```yaml
#ezpublish.yml
ezpublish:
    system:
        my_siteaccess:
            location_view:
                full:
                    article_test:
                        template: AcmeTestBundle:full:article_test.html.twig
                        params:
                            # This service must implement eZ\Bundle\EzPublishCoreBundle\Templating\ViewParameterProvider.
                            # getContentViewParameters() will be called
                            my_service: @some_defined_service
                            osTypes: [osx, linux, losedows]
                            secret: %secret%
                            # Parameters resolved by config resolver
                            # Supported syntax for parameters: $<paramName>[;<namespace>[;<scope>]]$
                            # e.g. full syntax: $my_setting;custom_namespace;ezdemo_site_admin$
                            # is equivalent of $configResolver->getParameter( 'my_setting', 'custom_namespace', 'ezdemo_site_admin );
                            default_ttl: $content.default_ttl$
                        match:
                            Id\Location: 144

                    another_test:
                        template: ::another_test.html.twig
                        match:
                            Id\Content: 142
                        params:
                            # some_other_defined_service service will be used,
                            # getMyViewParameters() will be called.
                            another_service:
                                service: @some_other_defined_service
                                method: getMyViewParameters
                            foo: bar
                            some_hash:
                                toto: tata
                                some: thing

                    custom_controller_test:
                        # The following will let you use your own custom controller for location #123
                        # (Here it will use AcmeTestBundle/Controller/DefaultController::viewLocationAction(),
                        # following the Symfony controller notation convention.
                        # Method viewLocationAction() must follow the same prototype as in the built-in ViewController
                        controller: AcmeTestBundle:Default:viewLocation
                        match:
                            Id\Location: 123
```

**Important**: Note that all configured parameters are only available in the template spotted in the template selection rule.

### Parameter provider example
In the configuration example above, `some_defined_service` would be like:

```php
<?php
namespace Acme\TestBundle;

use eZ\Bundle\EzPublishCoreBundle\Templating\ViewParameterProvider;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use Acme\TestBundle\SomeService;

class MyViewParameterProvider implements ViewParameterProvider
{
    private $someService;

    /**
     * Injected service is just an example. It can be whatever dependency you need
     */
    public function __construct( SomeService $someService )
    {
        $this->someService = $someService;
    }

    /**
     * Returns a hash of parameters to inject into the template associated to the provided $contentView.
     * Depending on the view context, location/content/block will be already set in $contentView.
     * DO NOT directly inject parameters into $contentView as parameters returned by this method will be namespaced to avoid name collisions.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $contentView
     *
     * @return array
     */
    public function getContentViewParameters( ContentViewInterface $contentView )
    {
        // Current location and content are available in $contentView
        $location = $contentView->getParameter( 'location' );
        $content = $contentView->getParameter( 'content' );

        return array(
            'foo' => $this->someService->giveMeFoo(),
            'some' => 'thing'
        );
    }
}
```

### Resulting view template
The view template would then be like:

```jinja
{% extends "eZDemoBundle::pagelayout.html.twig" %}

{% block content %}
<h1>{{ ez_render_field( content, 'title' ) }}</h1>

<p><strong>Secret:</strong> {{ secret }}</p>

<p><strong>OS Types:</strong></p>
{% for os in osTypes %}
    {{ os }}
    {% if not loop.last %}, {% endif %}
{% endfor %}

{# "my_helper" is namespaced by "my_service" according to configuration #}
<p>{{ my_service.my_helper.doSomething() }}</p>
<p>{{ my_service.some }}</p>
{% endblock %}

```

### Notes about caching
Enriched views will be treated the same as regular views regarding HttpCache.
As a result, the rule of thumb is to **avoid to inject specific context related parameters, especially when using a parameter provider service!**. 
If you need to display context-related information (e.g. user information), always do a sub-request with ESI.


## Matching custom controllers instead of templates
Matching templates is good, but even with parameter injection you may feel more comfortable using your own controller
to display a content/location, especially in advanced cases.

This is possible with the following piece of configuration:

```yaml
ezpublish:
    system:
        my_siteaccess:
            location_view:
                full:
                    custom_controller_test:
                        # The following will let you use your own custom controller for location #123
                        # (Here it will use AcmeTestBundle/Controller/DefaultController::viewLocationAction(),
                        # following the Symfony controller notation convention.
                        # Method viewLocationAction() must follow the same prototype as in the built-in ViewController
                        controller: AcmeTestBundle:Default:viewLocation
                        match:
                            Id\Location: 123
```

The only requirement here is that your action method has the same signature than `ViewController::viewLocation()` or `ViewController::viewContent()` (depending on what you're matching of course).

**viewLocation() signature**:

```php
<?php
/**
 * Main action for viewing content through a location in the repository.
 *
 * @param int $locationId
 * @param string $viewType
 * @param boolean $layout
 * @param array $params
 *
 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
 * @throws \Exception
 *
 * @return \Symfony\Component\HttpFoundation\Response
 */
public function viewLocation( $locationId, $viewType, $layout = false, array $params = array() )
```
    
**viewContent() signature**:

```php
<?php
/**
 * Main action for viewing content.
 *
 * @param int $contentId
 * @param string $viewType
 * @param boolean $layout
 * @param array $params
 *
 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
 * @throws \Exception
 *
 * @return \Symfony\Component\HttpFoundation\Response
 */
public function viewContent( $contentId, $viewType, $layout = false, array $params = array() )
```
    
> **Note**:
>
> Controller selection doesn't apply to `block_view` since you can already 
> [use your own controller to display blocks](https://confluence.ez.no/display/EZP/The+Page+FieldType#ThePageFieldType-Renderingblocks).

### Caching
Using your own controller, it is **your responsibility to define cache rules**, like for every custom controller !

So don't forget to set cache rules and the appropriate `X-Location-Id` header in the returned `Response` object.

[See built-in ViewController](https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Publish/Core/MVC/Symfony/Controller/Content/ViewController.php#L68) for more details on this.


## Overriding the built-in ViewController
One other way to keep control on what is passed to the view is to use your own controller instead of the built-in ViewController.

Base ViewController being defined as a service, with a service alias, this can be easily achieved from your bundle's configuration:

```yaml
parameters:
    my.custom.view_controller.class: Acme\TestBundle\MyViewController

services:
    my.custom.view_controller:
        class: %my.custom.view_controller.class%
        arguments: [@some_dependency, @other_dependency]

    # Change the alias here and make it point to your own controller
    ez_content:
        alias: my.custom.view_controller
```
    
**Warning** ! Doing so will completely override the built-in ViewController! Use this at your own risk!
