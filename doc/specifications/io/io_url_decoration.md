# IO URL decoration

> Added in 5.4 / 2014.11

It should be possible to set any prefix for images and binary files served by the application.
This includes absolute URIs to static hosts. It is also possible to use ConfigResolver parameters to set the prefix.

## Configuration
```yaml
ezpublish:
    default:
        io:
            url_prefix: "$var_dir$/$storage_dir$"
            # S3 binary data handler storing within $var_dir$ folder
            # url_prefix: "http://s3-eu-west-1.amazonaws.com/my.bucket/$var_dir$"
            # nginx pointed directly to ezpublish-legacy/var/ezdemo_site/storage
            # url_prefix: "http://static.site.com"
```

## Effect
Any binary file returned by the public API will be prefixed with the configured value. Example with prefix set to `http://static.site.com`:
```
eZ\Publish\API\Repository\Values\Content\Field Object
(
    [id:protected] => 6463
    [fieldDefIdentifier:protected] => image
    [value:protected] => eZ\Publish\Core\FieldType\Image\Value Object
        (
            [id] => 3/6/4/6/6463-1-eng-GB/kidding.png
            [alternativeText] =>
            [fileName] => kidding.png
            [fileSize] => 37931
            [uri] => http://static.com/images/3/6/4/6/6463-1-eng-GB/kidding.png
            [imageId] => 617-6463
            [inputUri] =>
        )
    [languageCode:protected] => eng-GB
)
```


### Legacy compatibility
Legacy still requires non absolute path to store images (var/site/storage/images...). In order to work around this, an
`UrlRedecorator`, that converts back and forth between the legacy uri prefix and the one in use in the application, has
been added. It is used in all places where a legacy URL is returned/expected, and takes care of making sure the value
is as expected.

## Changes

### Configuration variables

#### `io.url_prefix`
URI prefix added to all BinaryFile objects URI. Can be configured using `ezpublish.system.<scope>.io.url_prefix` (see
above). Defaults to `$var_dir$/$storage_dir$`. Ex: `var/ezdemo_site/storage`.

Used to configure the default UrlDecorator service (`ezpublish.core.io.default_url_decorator`, used by all binarydata
handlers to generate the URI of loaded files.

#### `io.legacy_url_prefix`
The URI prefix used by legacy for its uris. Not meant to be configured.

Defaults to `$var_dir$/$storage_dir$`. Ex: `var/ezdemo_site/storage`.

#### `io.root_dir`
The physical root dir where binary files are stored.

Defaults to `%webroot_dir%/$var_dir$/$storage_dir$`. Not meant to be overridden

### Services

#### url decorators
An UrlDecorator decorates and undecorates a given string (url) in some way. It has two mirror methods: `decorate` and
`undecorate`.

Two implementations are provided: `Prefix`, and `AbsolutePrefix`. They both add a prefix to an URL, but `AbsolutePrefix`
will ensure that unless the prefix is an external URL, the result will be prepended with /.

Three UrlDecorator services are introduced:
- `ezpublish.core.io.prefix_url_decorator`
  Used by the binarydata handlers to decorate all uris sent out by the API. Uses AbsolutePrefix.
- `ezpublish.core.io.image_fieldtype.legacy_url_decorator`
  Used via the UrlRedecorator (see below) by various legacy elements (Converter, Storage Gateway...) to generate its
  internal storage format for uris. Uses a Prefix, not an AbsolutePrefix, meaning that no leading / is added.

In addition, an UrlRedecorator service, `ezpublish.core.io.image_fieldtype.legacy_url_redecorator`, uses both decorators
above to convert URIs between what is used on the new stack, and what format legacy expects (relative urls from the
ezpublish root).
