# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

### API

* The `eZ\Publish\API\Repository\URLAliasService` requires implementing two new methods:
    ```php
    public function refreshSystemUrlAliasesForLocation(Location $location);
    public function deleteCorruptedUrlAliases(): int;
    ```

### SPI

* The `eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler` requires implementing a new method:
    ```php
    public function deleteCorruptedUrlAliases(Location $location): int;
    ```

## Deprecations


## Removed features
