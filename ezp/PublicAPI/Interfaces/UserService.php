<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\User\UserCreate;

use ezp\PublicAPI\Values\User\UserUpdate;

use ezp\PublicAPI\Values\User\User;

use ezp\PublicAPI\Values\User\UserGroup;

use ezp\PublicAPI\Values\User\UserGroupCreate;

use ezp\PublicAPI\Values\User\UserGroupUpdate;

/**
 * This service provides methods for managing users and user groups
 * @example Examples/user.php
 * @package ezp\PublicAPI\Interfaces
 */
interface UserService {

	/**
	 * Creates a new user group using the data provided in the ContentCreate parameter
	 * In 4.x in the content type parameter in the profile is ignored
	 * - the content type is determined via configuration and can be set to null.
	 * The returned version is published.
	 * @param UserGroupCreate $userGroupCreate a structure for setting all necessary data to create this user group
	 * @param UserGroup $parentGroup
	 * @return UserGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to create a user group
	 * @throws IllegalArgumentException if the input structure has invalid data
	 */
	public function createUserGroup(/*UserGroupCreate*/ $userGroupCreate, /*UserGroup*/ $parentGroup);

	/**
	 * Loads a user group for the given id
	 * @param int $id
	 * @return UserGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to create a user group
	 * @throws ezp\Base\Exceptio\NotFound if the user group with the given id was not found
	 */
	public function loadUserGroup($id);

	/**
	 * loads the sub groups of a user group
	 * @param UserGroup $userGroup
	 * @return array an array of {@link UserGroup}
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to read the user group
	 * @throws ezp\Base\Exceptio\NotFound if the user group with the given id was not found
	 */
	public function loadSubUserGroups(/*UserGroup*/ $userGroup);

	/**
	 * removes a user group
	 * the users which are not assigned to other groups will be deleted
	 * @param UserGroup $userGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to create a user group
	 * @throws ezp\Base\Exceptio\NotFound if the user group with the given id was not found
	 */
	public function deleteUserGroup(/*UserGroup*/ $userGroup);

	/**
	 *
	 * moves the user group to another parent
	 * @param \ezp\PublicAPI\Values\User\UserGroup $userGroup
	 * @param \ezp\PublicAPI\Values\User\UserGroup $newParent
	 * @throws \ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to move the user group
	 * @throws \ezp\Base\Exceptio\NotFound if the user group with the given id was not found
	 */
	public function moveUserGroup(/*UserGroup*/ $userGroup, /*UserGroup*/ $newParent);

	/**
	 *
	 * updates the group profile with fields and meta data.
	 * 4.x: If the versionUpdate is set in $userGroupUpdate, this method internally creates a content draft, updates ts with the provided data
	 * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
	 *
	 * @param UserGroup $userGroup
	 * @param UserGroupUpdate $userGroupUpdate
	 * @return UserGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to move the user group
	 * @throws ezp\Base\Exceptio\NotFound if the user group with the given id was not found
	 */
	public function updateUserGroup(/*UserGroup*/ $userGroup, /*UserGroupUpdate*/ $userGroupUpdate);

	/**
	 * Create a new user. The created user is published by this method.
	 * @param UserCreate $userCreate the data used for creating the user
	 * @param  array $parentGroups the groups of type {@link UserGroup} which are assigned to the user after creation
	 * @return User
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to move the user group
	 * @throws ezp\Base\Exceptio\NotFound if a user group was not found
	 */
	public function createUser(/*UserCreate*/ $userCreate, array $parentGroups);

	/**
	 * Loads a user
	 * @param integer $userId
	 * @return User
	 * @throws \ezp\Base\Exceptio\NotFound if a user with the given id was not found
	 */
	public function loadUser($userId);

	/**
	 * Loads a user for the given login and password
	 * @param string $login
	 * @param string $password the plain password
	 * @return User
	 * @throws ezp\Base\Exceptio\NotFound if a user with the given credentials was not found
	 */
	public function loadUserByCredentials($login,$password);

	/**
	 * This method deletes a user.
	 * @param User $user
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to delete the user
	 */
	public function deleteUser(/*User*/ $user);

	/**
	 * Updates a user.
	 * 4.x: If the versionUpdate is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
	 * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
	 * @param User $user
	 * @param UserUpdate
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to update the user
	 */
	public function updateUser(/*User*/ $user, /*UserUpdate*/ $userUpdate);

	/**
	 * Assigns a new user group to the user. If the user is already in the given user group this method does nothing.
	 * @param User $user
	 * @param UserGroup $userGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to assign the user group to the user
	 */
	public function assignUserToUserGroup(/*User*/ $user, /*UserGroup*/ $userGroup);

	/**
	 * Removes a user group from the user
	 * @param User $user
	 * @param UserGroup $userGroup
	 * @throws ezp\Base\Exceptio\UnAuthorized if the authenticated user is not allowed to remove the user group from the user
	 * @throws ezp\Base\Exceptio\Forbidden if the user is not in the given user group
	 */
	public function unAssignUssrFromUserGroup(/*User*/ $user, /*UserGroup*/ $userGroup);

	/**
	 * instanciates a user create class
	 * @param string $login the login of the new user
	 * @param string $email the email of the new user
	 * @param string $password the plain password of the new user
	 * @param $mainLanguageCode the main language for the underlying content object
	 * @param ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
	 * @return UserCreate
	 */
	public function newUserCreate($login, $email, $password, $mainLanguageCode, $contentType = null);

	/**
	 * instanciates a user group create class
	 * @param $mainLanguageCode the main language for the underlying content object
	 * @param ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
	 * @return UserGroupCreate
	 */
	public function newUserGroupCreate( $mainLanguageCode, $contentType = null);
	
}