<?php
/**
 * File containing the ContentVersion class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\values;

/**
 * @package ezp.persistence.content.values
 */
class ContentVersion 
{
	/**
	 * @AttributeType int
	 */
	public $versionNr;
	/**
	 * @AttributeType int
	 */
	public $modified;
	/**
	 * @AttributeType int
	 */
	public $creator;
	/**
	 * @AttributeType int
	 */
	public $created;
	/**
	 * @AttributeType int
	 */
	public $state;
	/**
	 * @AssociationType ezp.persistence.content.values.Content
	 */
	public $unnamed_Content_;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentField
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $field = array();
}
?>