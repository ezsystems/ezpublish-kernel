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


## Deprecations


No further changes are known in this release at the time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
