<?php
namespace ezp\persistence\user;
/**
 * @access public
 * @author root
 * @package ezp.persistence.user
 */
class Role 
{
	/**
	 * @AttributeType string
	 */
	public $name;
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AssociationType ezp.persistence.user.Policy
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Composition
	 */
	public $unnamed_Policy_ = array();
}
?>