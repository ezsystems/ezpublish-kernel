<?php
namespace ezp\persistence\content\values;
/**
 * @access public
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