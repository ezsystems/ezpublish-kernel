# Response taggers API

> added in ezpublish-kernel 6.8

ResponseTaggers will take a `Response`, a `ResponseConfigurator` and any value object, and will add tags to the Response
based on the value.

## Example
This will add the 'content-<contentId>`, 'location-<mainLocationId>` and `content-type-<contentTypeId>` tags to the
Response:

```php
$contentInfoResponseTagger->tag($response, $configurator, $contentInfo);
```

## The ResponseConfigurator
A `ResponseCacheConfigurator` configures an HTTP Response object: make the response public, add tags, set the shared max
age... It is provided to `ResponseTaggers` who use it to add the tags to the Response.

The `ConfigurableResponseCacheConfigurator` (`ezplatform.view_cache.response_configurator`) will follow the `view_cache`
configuration, and only enable cache if it is enabled in the configuration.

## Delegator and Value Taggers
Even though they share the same API, ResponseTaggers are of two types, reflected by their namespace: Delegator and Value.

Delegator Taggers will extract another value, or several, from the given value, and pass it on to another tagger. For
instance, a `ContentView` is covered by both the `ContentValueViewTagger` and the `LocationValueViewTagger`. The first will
extract the `Content` from the `ContentView`, and pass it to the `ContentInfoTagger`. The second will extract the `Location`,
and pass it to the `LocationViewTagger`.

## The Dispatcher Tagger
While it is more efficient to use a known tagger directly, sometimes you don't know what object you want to tag with.
The Dispatcher ResponseTagger will accept any value, and will pass it to every tagger registered with the service tag
`ezplatform.http_response_tagger`.
