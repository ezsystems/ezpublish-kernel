eZ Publish: Permissions, Requirements
=====================================


Introduction
------------

This document is divided into two sections. The first section presents the
permission system in eZ Publish 3/4. The second section discusses the
requirements of the permission system for eZ Publish 5.

eZ Publish 3/4
--------------

eZ Publish 3.x and by extension 4.x have a permission system witch by
default does not give you permission to anything, but by means of roles
and policies you can grant access to certain or all parts of the system.
These policies can have limitations, meaning they will only be applied
under certain conditions. Role assignments can optionally be applied
with limitations, presumably to be able to reuse roles for different
users. Roles can be assigned to both user groups and directly to users.

User [ <-> Group ]<-> Role -> Policy -> Limitations

Available Role assignment limitations are subtree and section, where
subtree refers to a subtree within a node (aka location) structure.

Issues with permission system:
* There is no way to extend these limitations without hacking the kernel.
* The kernel does not have an api that deal with limitations, so all
  kernel & extension code that deals with permissions needs to handle it
  on it's own. Leading to code duplication and also some times security
  issues caused by uncertainty around which api's check permissions & not.
* The system is not designed to to handle lots of role assignments pr users
  like is often the case in for instance eZ Teamroom.
* Some Limitations are implemented as O(n) leading to slower performance the
  more Limitations are used.
* user/selfedit is implemented as pure business logic, but should instead use
  limitations or some other way to separate the logic so it can be checked by
  the permission system like everything else.

eZ Publish 5
------------

The following requirements have been identified:

* Must haves:
    * Retain full data bc as 5.x and 4.x kernels will live side by side
    * Make all part of API deal with permissions internally without duplicating
      the permission code.
    * Make API to check permissions public for use in custom code as well
* Should haves:
    * Make it possible to extend the limitations in new kernel
* Could haves:
    * Improved performance where possible, and document limitations that are
      slow by design.
