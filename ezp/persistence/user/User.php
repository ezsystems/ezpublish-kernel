<?php
/**
 * File containing the User class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\user;

/**
 * @package ezp.persistence.user
 */
class User 
{
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AttributeType string
	 */
	public $login;
	/**
	 * @AttributeType string
	 */
	public $pwd;
	/**
	 * @AttributeType string
	 */
	public $hashAlg;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentTypeBase
	 */
	public $unnamed_ContentTypeBase_;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentTypeBase
	 */
	public $unnamed_ContentTypeBase_2;
}
?>