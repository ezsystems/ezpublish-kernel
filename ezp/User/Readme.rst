User module readme
==================

The User module (ezp\User) is an module for handling users, user groups, roles and policies.
It depends on ezp\Base and currently also ezp\Content, the latter because users and users groups
uses the content engine as inherited from eZ Publish 4.x. However this module is modeled is such
a way that persistence of users can be moved out of content model later.


Backward compatibility breakage
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* Roles can no longer be assigned to users, this is to align closer to how other systems behave
  to be able to use eg. ldap as backend for users without duplicating users to our storage.
  Retrieval does currently support returning policies that are assigned to users though, via
  User\Service->loadRolesByGroupId() & User\Service->loadPoliciesByUserId()

* Multiple Group assignments are no longer supported for same reason as above, multiple assignments
  of users is however still fully supported. This affects retrieval of inherited policies, given a
  tree like this:

  User Group
    |- Anonymous Group
    |    |- Anonymous User
    |    |- Administrator User
    |
    |- Administrator Group
         |- Administrator User
         |- Anonymous Group

  In this case "Administrator User" inherited policies from both groups as user is assigned to both,
  but "Anonymous User" does not as only the location of the user is taken into account.

  Technically this means policies are retrieved for:
      1. User Object it self (deprecated)
      2. All Groups in the paths of all Groups assigned to the User from bottom to top

* Roles can currently not be assigned with limitations. This will most likely change, but the
  reasoning for doing so was to simplify the complexity it causes for permissions. As well
  as the fact that for many policies this content centric set of limitation (SubTree & Section)
  does not make sense. So ideally we would like to expand Policy limitations so they can cover
  any use-cases Role assignment limitations provide.

* hiding/unhinding a subtree does not disable users or user groups, it only makes sure
  certain users trees are not browseable via content api's / module if a siteaccess
  is setup to not show hidden locations, use isEnabled