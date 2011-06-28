<?php
/**
 * File containing the Location class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\values;

/**
 * @package ezp.persistence.content.values
 */
class Location 
{
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AttributeType int
	 */
	public $position;
	/**
	 * @AttributeType bool
	 */
	public $hidden;
	/**
	 * @AttributeType bool
	 */
	public $invisible;
	/**
	 * @AttributeType string
	 */
	public $remoteId;
	/**
	 * @AssociationType ezp.persistence.content.values.Content
	 * @AssociationMultiplicity 1
	 */
	public $content;
	/**
	 * @AssociationType ezp.persistence.content.values.Location
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $child = array();
	/**
	 * @AssociationType ezp.persistence.content.values.Location
	 * @AssociationMultiplicity 0..1
	 */
	public $parent_10;
}
?>