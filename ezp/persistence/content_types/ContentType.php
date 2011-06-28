<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content_types;

/**
 * @package ezp.persistence.content_types
 */
class ContentType extends ContentTypeBase 
{
	/**
	 * @AttributeType string
	 */
	public $remoteId;
	/**
	 * @AttributeType string
	 */
	public $urlAliasSchema;
	/**
	 * @AttributeType string
	 */
	public $nameSchema;
	/**
	 * @AttributeType bool
	 */
	public $container;
	/**
	 * @AttributeType string
	 */
	public $initialLanguage;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentTypeGroup
	 * @AssociationMultiplicity 0..*
	 */
	public $unnamed_ContentTypeGroup_ = array();
	/**
	 * @AssociationType ezp.persistence.content_types.FieldDefinition
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $fieldDefinition = array();
}
?>