# Custom Domain Types
Note: This is currently a Core feature, as in internal to Core/Repository.

## Description
Allows Repository to return sub classes of Content with added functionality consistently from all API methods.


## Rational
With the introduction of Repository API in eZ Publish 5.0, a concept of Content Domain types of User and UserGroup
was introduced, where these abstract API classes extends the abstract API Content class.

However as these also happens to be content (for the time being), it was possible to both get the same content as
a Content class using Content and Search Service, and as User(Group) using UserService.

Meaning you could not use Content( type: user ) with UserService as it only accepts instances of User(Group).

This complicated "UserLand" code as well as implementation of UserService as quite some logic needed to be duplicated.

## Solution
To remedy this a `DomainTypeMapper` interface has been introduces to be used by (Content)DomainMapper class when
building content objects. These are all placed in `eZ\Publish\Core\Repository\Helper` namespace, where no code may
depends on Repository or it's Services as these classes are used by repository and services themselves.

Besides benefit of getting the sub Content types directly from search, additional side effect is improved speed of
loading UserGroup significantly as it now uses SPI (cached) calls to set UserGroup->parentId, and ->subGroupCount
as was using search api has been disabled and set to null (property was deprecated in 5.3.3).

## Usage
TBD: Needs further decoupling in Repository to be able to inject custom versions.