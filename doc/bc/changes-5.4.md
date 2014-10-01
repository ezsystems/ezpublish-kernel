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
