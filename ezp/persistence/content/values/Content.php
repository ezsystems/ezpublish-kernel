<?php
namespace ezp\persistence\content\values;
/**
 * @access public
 * @author root
 * @package ezp.persistence.content.values
 */
class Content 
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
	public $id;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentVersion
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $versionInfo = array();
	/**
	 * @AssociationType ezp.persistence.content.values.Location
	 * @AssociationMultiplicity 0..*
	 */
	public $location = array();
}
?>