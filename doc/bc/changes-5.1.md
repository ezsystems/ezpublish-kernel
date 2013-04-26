# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.


## Changes

### API

* Repository\UserService::unAssignUserFromUserGroup()
  Has been changed to throw BadStateException if only one
  UserGroup is left assigned on User as users without any
  user groups does not make any sense in eZ Publish.
  As long as users are also in content model, removing all
  locations on the corresponding content is still possible.

No further changes known in this release at time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).


## Deprecations

No known deprecations in this release at time of writing.
