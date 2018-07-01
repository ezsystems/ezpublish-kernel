# Variation purging

> From eZ Platform 2015.05

## Synopsis and example

Makes it possible to clear all variations generated for an alias. Uses the liip:imagine:cache:remove script.
Example (the `-v` option will log removals to the console), removing variations for the `large` and `gallery` aliases :

```shell
php bin/console liip:imagine:cache:remove --filters=large --filters=gallery -v
```

## Internal changes

Two items have been refactored/introduced:
- purging of variations from the IORepositoryResolver is done by a `VariationPurger`
  Aliased service: `ezpublish.image_alias.variation_purger`
- generation of the alias path from the original path is done by a `VariationPathGenerator`
  Aliased service: `ezpublish.image_alias.variation_path_generator`

### 2015.05 and above

- Variation Purger: `ezpublish.image_alias.variation_purger.io`
  Uses the IOService to delete the directory `_aliases/<aliasName>`
- Path Generator: `ezpublish.image_alias.variation_path_generator.alias_directory`
  Stores variations in the `_aliases` folder, in a subfolder named after the alias: `_aliases/<aliasName>`

A set of Purger / Path Generator is added for releases from 2015.05. It relies on the new storage method for aliases:
they're no longer stored in the original's folder, but in a dedicated folder. This makes purging as simple as removing
the alias folder.

### Earlier versions

- Variation Purger: `ezpublish.image_alias.variation_purger.legacy_storage_image_file`
  Uses the `LegacyStorageImageFileList` to iterate over originals, and clears the files using the IOService.
- Path Generator: `ezpublish.image_alias.variation_path_generator.original_directory`
  Stores variations in the same folder than the original, suffixing the name with `_<aliasName>`.

Uses an ImageFileList service. It comes with a Legacy Storage implementation that lists original images from the
`ezimagefile` database table. Aliases for each original is tested, and removed if found and applicable using the IOService.

It is much more resource intensive, but it is the best option given the way variations are stored.
