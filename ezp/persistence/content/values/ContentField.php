<?php
namespace ezp\persistence\content\values;
/**
 * @access public
 * @package ezp.persistence.content.values
 */
class ContentField 
{
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AttributeType string
	 */
	public $type;
	public $value;
	/**
	 * @AttributeType string
	 */
	public $language;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentVersion
	 */
	public $unnamed_ContentVersion_;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentCreateStruct
	 */
	public $unnamed_ContentCreateStruct_;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentUpdateStruct
	 */
	public $unnamed_ContentUpdateStruct_;
}
?>