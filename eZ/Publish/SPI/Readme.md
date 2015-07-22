# eZ Publish 5.x API

This package is a split of the [eZ Publish 5](https://github.com/ezsystems/ezpublish-kernel) SPI (persistence interfaces).

It can be used as a dependency, instead of the whole ezpublish-kernel, by packages implementing custom eZ Publish
storage engines, or by any package that requires classes from the `eZ\Publish\SPI` namespace.

The repository is read-only, automatically updated from https://github.com/ezsystems/ezpublish-kernel.

Refer to the [main project's README.md](https://github.com/ezsystems/ezpublish-kernel/blob/master/Readme.md)
for further information.

## Requiring ezpublish-api in your project
```yaml
"require": {
  "ezsystems/ezpublish-spi": "~5.0"
}
```

## Copyright & license
eZ Systems AS & GPL 2.0
