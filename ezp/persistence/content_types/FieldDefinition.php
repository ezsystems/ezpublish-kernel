<?php
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