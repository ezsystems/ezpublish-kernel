<?php
namespace ezp\persistence\content\criteria;
/**
 * @access public
 * @author root
 * @package ezp.persistence.content.criteria
 */
class PermissionCriteria extends Criteria 
{
	/**
	 * @AttributeType int
	 */
	public $userId;
	/**
	 * @AttributeType string
	 */
	public $operation;
}
?>