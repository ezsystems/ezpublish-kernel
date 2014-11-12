# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Stash update brings a slight change to the configuration format.
  Instead of referring to `handlers`, it is now using the term `drivers`.

  ```diff
  stash:
      caches:
          default:
  -           handlers:
  +           drivers:
                  # When using multiple webservers, you must use Memcache or Redis
                  - FileSystem
              inMemory: true
          registerDoctrineAdapter: false
          # On Windows, using FileSystem, to avoid hitting filesystem limitations
          # you need to change the keyHashFunction used to generate cache directories to "crc32"
          # FileSystem
          #    keyHashFunction: crc32
  ```

* Property `id` for `eZ\Publish\Core\FieldType\Image\Value` (value object for `ezimage` FieldType) has changed.
  It wrongly contained the full image path, including the storage directory (e.g. `var/ezdemo_site/storage/images/`.
  To get the full path, use `uri` property.
  Ref: [EZP-23349](https://jira.ez.no/browse/EZP-23349)
  
* `border` image filter configuration has changed. It now takes a 3rd parameter for color (`#000` by default).
  This parameter accepts RGB hexa values. 1st is still border thickness for horizontal, 2nd thickness for vertical.
  
  ```yaml
  ezpublish:
      system:
          my_siteaccess:
              image_variations:
                  my_border_variation:
                      reference: null
                      filters:
                          # Adding a white border, 10px thick.
                          - { name: border, params: [10, 10, "#fff"] }
  ```
  
* `resize` image filter configuration has changed. It now follows the configuration provided in LiipImagineBundle:

  ```yaml
  ezpublish:
      system:
          my_siteaccess:
              image_variations:
                  my_border_variation:
                      reference: null
                      filters:
                          - { name: resize, params: {size: [1080, 1024]} }
  ```

* Language filtering has been changed to respect always available flag, this affects:
  - Criterion\LanguageCode second argument `$matchAlwaysAvailable` changed to true by default
  - ContentService methods with `$useAlwaysAvailable` has been changed to be true by default, affects:
    - loadContentByContentInfo
    - loadContentByVersionInfo
    - loadContent
    - loadContentByRemoteId

  If you have code that expects exception(load) or no content (search) if not a specific language
  exists, you should then review your code and consider setting these properties to false.
  
* HTTP cache is now always purged using a single HTTP request, being emulated (`local`) or real, always using `X-Location-Id` header.
  `X-Group-Location-Id` is not used any more and is thus deprecated.
  
  As a result, semantic setting `ezpublish.http_cache.purge_type` now only accepts `local` or `http` as values.
  `multiple_http` and `single_http` are deprecated and are now considered as `http`. You may change them to use `http`:
  
  ```diff
    ezpublish:
        http_cache:
    -        purge_type: multiple_http
    +        purge_type: http
    ```
* The usual `IOService` is overridden by a `TolerantIOService`. It replaces the previous `try/catch` blocks in
  binary FieldTypes external storage. When one of the IO layers returns a not found, a MissingBinaryFile will be
  returned, with fake properties: the requested id, no uri, no file size... if logging is enabled, an info message
  will still be logged.

* `ViewCaching` legacy setting is now enforced and injected in legacy kernel when booted. This is to avoid persistence/Http
  cache clear not working when publishing content.

## Deprecations

* `imagemagick` siteaccess settings are now deprecated. It is mandatory to remove them.
  An exception will be thrown when compiling the container to remind to remove them

  ```diff
  ezpublish:
      system:
          my_siteaccess:
  -            imagemagick:
  -                pre_parameters:
  -                post_parameters:
  ```

* `imagemagick` settings at the application level (`convert` path and filters definitions) have been deprecated.
  They will be removed in v6.0.

* `mimeType` property from BinaryFile is deprecated. The value might not be present for some IO handlers.
  Use `getMimeType` from the IOService instead.

* `IOService::getExternalPath()` and `IOService::getInternalPath()` have been removed.
  `getInternalPath()`, that returned the handler level path, can be obtained using the uri property.

  and by getting the URI from the returned value object.
  `getExternalPath()`, that returns the id seen from the IOService, can be replaced by `IOService::loadBinaryFileByUri()`.
  
* Semantic setting `ezpublish.http_cache.timeout` has been deprecated and is no longer used. It can be safely removed.

  ```diff
  ezpublish:
      http_cache:
  -        timeout: 1
  ```
  
* *Identity definer* services (using `ezpublish.identity_definer` tag) are deprecated in favor of 
  [custom Context Providers from `FOSHttpCacheBundle`](http://foshttpcachebundle.readthedocs.org/en/latest/reference/configuration/user-context.html#custom-context-providers).
  
  `ezpublish.identity_definer` service tag and related classes/interfaces will be removed in v6.0
  
No further changes are known in this release at the time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).


## Removed features

* Following image filters are not supported any more and have been removed:
  * `flatten`. Obsolete, images are automatically flattened.
  * `bordercolor`. Not applicable any more.
  * `border/width`. Not applicable any more.
  * `colorspace/transparent`
  * `colorspace`

* `IOService::getMetadata()` has been removed
  ImageSize metadata is now handled at fieldtype level, in the Legacy
  conversion, and upon upload on the local file.
