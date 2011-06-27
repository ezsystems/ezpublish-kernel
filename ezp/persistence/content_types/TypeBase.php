<?php
namespace ezp\persistence\content_types;
/**
 * @access public
 * @author root
 * @package ezp.persistence.content_types
 */
class TypeBase 
{
	/**
	 * @AttributeType string
	 */
	public $identifier;
	/**
	 * @AttributeType array
	 */
	public $name;
	/**
	 * @AttributeType array
	 */
	public $description;
	/**
	 * @AttributeType int
	 */
	public $id;
}
?>