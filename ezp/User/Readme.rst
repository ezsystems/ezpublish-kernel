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

* Roles can currently not be assigned with limitations. This will most likely change, but the
  reasoning for doing so was to simplify the complexity it causes for permissions. As well
  as the fact that for many policies this content centric set of limitation (SubTree & Section)
  does not make sense. So ideally we would like to expand Policy limitations so they can cover
  any use-cases Role assignment limitations provide.