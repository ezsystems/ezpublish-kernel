<?php
namespace ezp\persistence\content\criteria;
/**
 * @access public
 * @author root
 * @package ezp.persistence.content.criteria
 */
class ContentFieldCriteria extends Criteria 
{
	/**
	 * @AttributeType ezp.persistence.content.criteria.Operator
	 */
	public $opaerator;
	/**
	 * @AttributeType string
	 */
	public $fieldIdentifier;
	public $value;
}
?>