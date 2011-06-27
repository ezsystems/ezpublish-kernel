<?php
namespace ezp\persistence\user;
/**
 * @access public
 * @package ezp.persistence.user
 */
interface UserHandler 
{

	/**
	 * @access public
	 * @param ezp.persistence.user.User user
	 * @ParamType user ezp.persistence.user.User
	 */
	public function createUser(User $user);

	/**
	 * @access public
	 * @param int userId
	 * @ParamType userId int
	 */
	public function deleteUser($userId);

	/**
	 * @access public
	 * @param ezp.persistence.user.User user
	 * @ParamType user ezp.persistence.user.User
	 */
	public function updateUser(User $user);

	/**
	 * @access public
	 * @param ezp.persistence.user.Role role
	 * @return ezp.persistence.user.Role
	 * @ParamType role ezp.persistence.user.Role
	 * @ReturnType ezp.persistence.user.Role
	 */
	public function createRole(Role $role);

	/**
	 * @access public
	 * @param ezp.persistence.user.Role role
	 * @ParamType role ezp.persistence.user.Role
	 */
	public function updateRole(Role $role);

	/**
	 * @access public
	 * @param int roleId
	 * @ParamType roleId int
	 */
	public function deleteRole($roleId);

	/**
	 * @access public
	 * @param int userId
	 * @return array
	 * @ParamType userId int
	 * @ReturnType array
	 */
	public function getPermissions($userId);

	/**
	 * @access public
	 * @param int contentId
	 * @param int roleId
	 * @param limitation
	 * @ParamType contentId int
	 * @ParamType roleId int
	 */
	public function assignRole($contentId, $roleId, $limitation);

	/**
	 * @access public
	 * @param int contentId
	 * @param int roleId
	 * @ParamType contentId int
	 * @ParamType roleId int
	 */
	public function removeRole($contentId, $roleId);
}
?>