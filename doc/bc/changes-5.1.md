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


## Deprecations

No known deprecations in this release at time of writing.


## Removals

* Content->contentType, ContentInfo->contentType & ContentInfo->getContentType()
  This properties & method was deprecated as part of 5.0 and now removed in
  favour of ContentInfo->contentTypeId for use with sub requests via own API.

* Content->relations & Content->getRelations()
  These property & method was deprecated as part of 5.0 and now removed in favour
  of using dedicated API to get relations as it was causing issues for permission
  system and in some cases causing eagerly loading of large sets of recursive
  relations.


No further changes known in this release at time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
