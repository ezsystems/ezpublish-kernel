# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes


## Deprecations

* Integer field type `IntegerValueValidator` has changed to be inline with the Float field type validator.
 `false` is considered deprecated for `minIntegerValue` and `maxIntegerValue`, and will be removed in 7.0.
  Use `null` instead of `false` if you want to deactivate these validators.
