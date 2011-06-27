<?php
namespace ezp\persistence\content\criteria;
/**
 * @access public
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