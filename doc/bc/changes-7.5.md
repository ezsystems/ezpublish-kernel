# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Password quality checks are by default more strict than before.

  Passwords must be at least 10 characters long, and must include upper and lower case, and digits. This
  new default can be changed by editing the user account field type in the User content type.

  This applies to the standard User content type if you install eZ Platform from scratch, and to any new
  User content types you create after install/upgrade.

  It does not apply to the standard User content type if you upgrade from an earlier release. If you make
  this change in your existing User content type, existing users with shorter/simpler passwords will
  still be able to log in, but they will not be able to make new passwords that violate the requirements.

  Similarly, the default password of the Admin user is not changed. But you must of course change it
  before going live with a new project, and when you do, the new rules come into effect.
