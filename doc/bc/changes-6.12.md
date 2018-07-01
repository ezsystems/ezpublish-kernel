# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

- Increase password security
  EZP-24744 - Increase password security introduced two new user password hash types, `PASSWORD_HASH_BCRYPT` and
  `PASSWORD_HASH_PHP_DEFAULT`. Either one allows the password to be stored with encryption instead of the previous
  default, which uses MD5 hashing. `PASSWORD_HASH_BCRYPT` uses Blowfish (bcrypt). `PASSWORD_HASH_PHP_DEFAULT` is
  the new default setting, this uses whichever method PHP considers appropriate. Currently this is bcrypt, but
  this may change over time.
  Caution - Using either of these new types requires that the length of the `password_hash` column of the `ezuser`
  database table is increased from the current value of 50 to 255, see the updated database schema.

## Deprecations

## Removed features
