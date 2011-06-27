<?php
namespace ezp\persistence\content\values;
/**
 * @access public
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