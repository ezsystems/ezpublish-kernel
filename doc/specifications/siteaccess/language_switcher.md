# Language Switcher

## Description
A content can be translated in several languages. Those languages are configured in the system and exposed in
SiteAccesses via a prioritized list of languages:

```yaml
ezpublish
    system:
        eng:
            languages: [eng-GB]
        # In fre siteaccess, fre-FR is always preferred, and fallback to eng-GB if needed.
        fre:
            languages: [fre-FR, eng-GB]
```

When visiting a content, it may be useful to let the user switch from one translation to another, more appropriate to him.
This is precisely the goal of the language switcher.

## Solution
The language switcher relies on the [Cross-SiteAccess linking feature](cross_siteaccess_links.md) to generate links to
the content's translation.

It adds the concept of **RouteReference**, which works in the same way of Symfony's
`ControllerReference` for sub-requests. A `RouteReference` represents a route (to a location object, a declared route...)
with its parameters and can be passed to the `Router` for link generation.
The advantage of a `RouteReference` is that its params can be modified later (e.g. to generate a link to the same
location in several different languages).

## Usage
### Configuration
Configuration is not mandatory, but can help to distinguish which SiteAccesses can be considered *translation SiteAccesses*.

```yaml
ezpublish:
    siteaccess:
        default_siteaccess: eng
        list:
            - ezdemo_site
            - eng
            - fre
            - ezdemo_site_admin

    ezdemo_frontend_group:
        - ezdemo_site
        - eng
        - fre

    # ...

    system:
        # Specifying which SiteAccesses are used for translation
        ezdemo_frontend_group:
            translation_siteaccesses: [fre, eng]
        eng:
            languages: [eng-GB]
        fre:
            languages: [fre-FR, eng-GB]
```

> If configuration is not provided, *related SiteAccesses* will be used instead.
> SiteAccesses are considered *related* when they share:
>
> * The same repository
> * The same root location Id (see [Multisite feature](../multisite/design_routing.md))

### In a template
To generate a language switch link, you need to generate the `RouteReference`, with the `language` parameter.
This can easily be done with `ez_route()` Twig function:

```jinja
{# Given that "location" variable is a valid Location object #}
<a href="{{ url( ez_route( location, {"language": "fre-FR"} ) ) }}">{{ ez_content_name( content ) }}</a>

{# Generating a link to a declared route instead of Location #}
<a href="{{ url( ez_route( 'my_route', {"language": "fre-FR"} ) ) }}">My link</a>
```

You can also omit the route, in this case, the current route will be used (i.e. switch the current page):

```jinja
{# Using Twig named parameters #}
<a href="{{ url( ez_route( params={"language": "fre-FR"} ) ) }}">My link</a>

{# Identical to the following, using ordered parameters #}
<a href="{{ url( ez_route( null, {"language": "fre-FR"} ) ) }}">My link</a>
```

### Using sub-requests
When using sub-requests, you lose the context of the master request (e.g. current route, current location...).
This is because sub-requests can be displayed separately, with ESI or Hinclude.

If you want to render language switch links in a sub-request with a correct `RouteReference`, you must pass it as an
argument to your sub-controller from the master request.

```jinja
{# Render the language switch links in a sub-controller #}
{{ render( controller( 'AcmeTestBundle:Default:languages', {'routeReference': ez_route()} ) ) }}
```

```php
namespace Acme\TestBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;

class DefaultController extends Controller
{
    public function languagesAction( RouteReference $routeRef )
    {
        return $this->render( 'AcmeTestBundle:Default:languages.html.twig', array( 'routeRef' => $routeRef ) );
    }
}
```

```jinja
{# languages.html.twig #}

{# Looping over all available languages to display the links #}
{% for lang in ezpublish.availableLanguages %}
    {# This time, we alter the "siteaccess" parameter directly. #}
    {# We get the right siteaccess with the help of ezpublish.translationSiteAccess() helper #}
    {% do routeRef.set( "siteaccess", ezpublish.translationSiteAccess( lang ) ) %}
    <a href="{{ url( routeRef ) }}">{{ lang }}</a><br />
{% endfor %}
```

* `ezpublish.translationSiteAccess( language )` returns the SiteAccess name for provided language (or `null ` if it cannot be found)
* `ezpublish.availableLanguages()` returns the list of available languages.

### Using PHP
You can easily generate language switch links from PHP too, with the `RouteReferenceGenerator` service:

```php
// Assuming we're in a controller
/** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface $routeRefGenerator */
$routeRefGenerator = $this->get( 'ezpublish.route_reference.generator' );
$routeRef = $routeRefGenerator->generate( $location, array( 'language' => 'fre-FR' );
$link = $this->generateUrl( $routeRef );
```

You can also retrieve all available languages with the `TranslationHelper`:

```php
/** @var \eZ\Publish\Core\Helper\TranslationHelper $translationHelper */
$translationHelper = $this->get( 'ezpublish.translation_helper' );
$availableLanguages = $translationHelper->getAvailableLanguages();
```
