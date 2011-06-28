<?php
/**
 * File containing the FieldDefinition class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content_types;

/**
 * @package ezp.persistence.content_types
 */
class FieldDefinition extends TypeBase 
{
	/**
	 * @AttributeType string
	 */
	public $fieldGroup;
	/**
	 * @AttributeType int
	 */
	public $position;
	/**
	 * @AttributeType string
	 */
	public $fieldType;
	/**
	 * @AttributeType bool
	 */
	public $translatable;
	/**
	 * @AttributeType bool
	 */
	public $required;
	/**
	 * @AttributeType bool
	 */
	public $infoCollector;
	/**
	 * @AttributeType array
	 */
	public $fieldTypeConstraints;
	public $defaultValue;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentType
	 * @AssociationMultiplicity 1
	 */
	public $unnamed_ContentType_;
}
?>