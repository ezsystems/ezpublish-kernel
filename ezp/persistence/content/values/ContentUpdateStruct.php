<?php
namespace ezp\persistence\content\values;
/**
 * @package ezp.persistence.content.values
 */
class ContentUpdateStruct 
{
	/**
	 * @AttributeType int
	 */
	public $sectionId;
	/**
	 * @AttributeType int
	 */
	public $userId;
	/**
	 * @AttributeType array
	 */
	public $newParents;
	/**
	 * @AttributeType array
	 */
	public $removeLocations;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentField
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $fields = array();
}
?>