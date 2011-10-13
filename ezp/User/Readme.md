#User module readme

The User module (ezp\User) is an module for handling users, user groups, roles and policies. It depends on ezp\Base and currently also ezp\Content, the latter because users and users groups uses the content engine as inherited from eZ Publish 4.x. However this module is modeled is such a way that persistence of users can be moved out of content model later.

##Overview
ezp\User* contains a set of domain objects and a service for dealing with those, some of the domain objects have a Proxy (lazy loading) and a Concrete implementation with a interface shared between them. The Files involved are:

	ezp/User.php								| User interface, extends Groupable & ModelDefinition
	ezp/User/Concrete.php				| Concreate User class, implements User
	ezp/User/Proxy.php					|  Proxy User class, implements User
	ezp/User/Group.php					| Group interface, extends Groupable
	ezp/User/Group/Concrete.php	| Concreate Group class, implements Group
	ezp/User/Group/Proxy.php		|  Proxy Group class, implements Group
	ezp/User/Groupable.php			| Groupable interface (impl can be grouped)
	ezp/User/Policy.php					| Concreate Policy class
	ezp/User/Readme.md					| This file
	ezp/User/Role.php						| Role interface, extends ModelDefinition
	ezp/User/Role/Concrete.php		| Concreate Role class, implements Role
	ezp/User/Role/Proxy.php			|  Proxy Role class, implements Role
	ezp/User/Service.php					|  User Service, provides methods for User operations



##Backward compatibility breakage
* Roles can no longer be assigned to users, this is to align closer to how other systems behave to be able to use eg. ldap as backend for users without duplicating users to our storage. Retrieval does currently support returning policies that are assigned to users though, via `$userService->loadRolesByGroupId()` & `$userService->loadPoliciesByUserId()`

* Multiple Group assignments are no longer supported for same reason as above, multiple assignments of users is however still fully supported. This affects retrieval of inherited policies, given a tree like this:

		User Group
		|- Anonymous Group
		|   |- Anonymous User
		|   |- Administrator User
		|
		|- Administrator Group
		    |- Administrator User
		    |- Anonymous Group

	In this case "Administrator User" inherited policies from both groups as user is assigned to both, but "Anonymous User" does not as only the location of the user is taken into account.

	Technically this means policies are retrieved for:
	1. User Object it self (deprecated)
	2. All Groups in the paths of Groups assigned to the User from bottom to top

* Roles can currently not be assigned with limitations. This will most likely change, but the reasoning for doing so was to simplify the complexity it causes for permissions. As well as the fact that for many policies this content centric set of limitation (SubTree & Section) does not make sense. So ideally we would like to expand Policy limitations so they can cover any use-cases Role assignment limitations provide.

* hiding/unhinding a subtree does not disable users or user groups, it only makes sure certain users trees are not browseable via content api's / module if a siteaccess is setup to not show hidden locations, use isEnabled

##Permissions API
Unlike eZ Publish permissions API centers around Models, and the idea is that you at some point can extend it more directly instead of having to write your own separate module logic to extend the content model.

In your everyday work with the API you should not have to deal with the Permissions at all, they are handled by the service layer for you unless documented differently.

However in some cases you might need to check permission access, and here are the api's involved:

###hasAccessTo()
    $user->hasAccessTo( $module, $function )
This is the same low level permission api you can also find in eZ Publish. Similarly it returns either a bool value or an array of limitations. The limitations is internal, and you should not depend on it's format. Example of use:

      $user->hasAccessTo( 'user', 'login' )

hasAccessTo can be usefully if you need to check access to some resource before loading it, but in most cases the next one is preferred.

###canUser
    $repository->canUser( $function, ModelDefinition $model[, Model $assignment[, array &$deniedBy ]] )

New api that deal with instances of objects, often used in service layer to make sure user actually have access to delete / create / update an object. Only objects that implements ModelDefinition are supported, currently Content, User, Content\Type, User\Role, Content\Section and Content\Language.
@todo Add doc on available permission functions on these Objects.

This api uses the response from `$user->hasAccessTo()` where `$user` is the current user as set on `$repository->getUser()` (optionaly with: `$repository->setUser( User $user )` ), and potential limitations is then given to closure functions returned by `$model::defintion() for validation.

*Arguments:*

* $function The function you are checking permissions for: create / delete / update
* $model The object to check permissions against, contains meta information about the permission *module* involved as well as logic for limitations.
* $assignment Is an extra object needed in the case of some functions:
	1. 'create' on Content, in this case it needs to be the parent Location where Content is created.
	2. 'assign' on Section, needs to be the Content that section is assigned to.
* $deniedBy Is an optional by reference array that can be used for debugging permissions as it will be filled with all limitations that return false in the order they where executed (order of limitations from $user->hasAccessTo())
