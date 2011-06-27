<?php
namespace ezp\persistence\content_types;
/**
 * @access public
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