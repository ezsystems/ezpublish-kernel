<?php
/**
 * File containing the UserHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\user;

/**
 * @package ezp.persistence.user
 */
interface UserHandler 
{

	/**
	 * @param ezp.persistence.user.User user
	 * @ParamType user ezp.persistence.user.User
	 */
	public function createUser(User $user);

	/**
	 * @param int userId
	 * @ParamType userId int
	 */
	public function deleteUser($userId);

	/**
	 * @param ezp.persistence.user.User user
	 * @ParamType user ezp.persistence.user.User
	 */
	public function updateUser(User $user);

	/**
	 * @param ezp.persistence.user.Role role
	 * @return ezp.persistence.user.Role
	 * @ParamType role ezp.persistence.user.Role
	 * @ReturnType ezp.persistence.user.Role
	 */
	public function createRole(Role $role);

	/**
	 * @param ezp.persistence.user.Role role
	 * @ParamType role ezp.persistence.user.Role
	 */
	public function updateRole(Role $role);

	/**
	 * @param int roleId
	 * @ParamType roleId int
	 */
	public function deleteRole($roleId);

	/**
	 * @param int userId
	 * @return array
	 * @ParamType userId int
	 * @ReturnType array
	 */
	public function getPermissions($userId);

	/**
	 * @param int contentId
	 * @param int roleId
	 * @param limitation
	 * @ParamType contentId int
	 * @ParamType roleId int
	 */
	public function assignRole($contentId, $roleId, $limitation);

	/**
	 * @param int contentId
	 * @param int roleId
	 * @ParamType contentId int
	 * @ParamType roleId int
	 */
	public function removeRole($contentId, $roleId);
}
?>