<?php
namespace ezp\persistence\user;
/**
 * @access public
 * @package ezp.persistence.user
 */
class Policy 
{
	/**
	 * @AttributeType string
	 */
	public $module;
	/**
	 * @AttributeType string
	 */
	public $moduleFunction;
	/**
	 * @AttributeType array
	 */
	public $limitations;
	/**
	 * @AssociationType ezp.persistence.user.Role
	 */
	public $unnamed_Role_;
}
?>