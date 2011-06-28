<?php
/**
 * File containing the ContentCreateStruct struct
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\values;

/**
 * @package ezp.persistence.content.values
 */
class ContentCreateStruct 
{
	/**
	 * @AttributeType string
	 */
	public $name;
	/**
	 * @AttributeType string
	 */
	public $type;
	/**
	 * @AttributeType int
	 */
	public $sectionId;
	/**
	 * @AttributeType int
	 */
	public $ownerId;
	/**
	 * @AttributeType int
	 */
	public $parentLocation;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentField
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $field = array();
}
?>