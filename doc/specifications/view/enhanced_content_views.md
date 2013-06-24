# Enhanced views for Content/Location

In some cases, displaying a content/location via the built-in ViewController
is not sufficient and will lead you to do many sub-requests in order to access different parameters.

Typical use cases are access to:

* Settings (either coming from ConfigResolver or ServiceContainer)
* Current content's ContentType object
* Current location's parent
* Current location's children count
* Main location and alternative locations for the current content
* etc…

In those cases, you may want to use your own controller to display the current 
content/location instead of using the built-in `ViewController`.

## Description
This feature covers 2 general use cases:

* Lets you configure a custom controller with the configured matcher rules.
* Lets you override the built-in view controller in a clean way.


## Matching custom controllers
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
