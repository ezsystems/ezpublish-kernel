<?php
/**
 * File containing the Policy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\user;

/**
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