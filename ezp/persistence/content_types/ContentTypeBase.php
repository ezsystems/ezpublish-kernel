<?php
/**
 * File containing the ContentTypeBase class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content_types;

/**
 * @package ezp.persistence.content_types
 */
class ContentTypeBase extends TypeBase 
{
	/**
	 * @AttributeType int
	 */
	public $created;
	/**
	 * @AttributeType int
	 */
	public $modified;
	/**
	 * @AssociationType ezp.persistence.user.User
	 * @AssociationMultiplicity 1
	 * @AssociationKind Aggregation
	 */
	public $creator;
	/**
	 * @AssociationType ezp.persistence.user.User
	 * @AssociationMultiplicity 1
	 * @AssociationKind Aggregation
	 */
	public $modifier;
}
?>