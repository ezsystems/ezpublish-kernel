# IO URL decoration

> Added in 5.4 / 2014.09

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


### Legacy compatiblity
Legacy still requires non absolute path to store images (var/site/storage/images...). In order to work around this, an
`UrlRedecorator`, that converts back and forth between the legacy uri prefix and the one in use in the application, has
been added. It is used in all places where a legacy URL is returned/expected, and takes care of making sure the value
is as expected.
