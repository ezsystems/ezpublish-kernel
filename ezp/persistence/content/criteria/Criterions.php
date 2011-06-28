<?php
namespace ezp\persistence\content\criteria;
/**
 * @package ezp.persistence.content.criteria
 */
class Criterions extends Criteria 
{
	/**
	 * @AttributeType ezp.persistence.content.criteria.LogicOperator
	 */
	public $operator;
	/**
	 * @AssociationType ezp.persistence.content.criteria.Criterions
	 * @AssociationMultiplicity 1..*
	 * @AssociationKind Composition
	 */
	public $childs = array();
	/**
	 * @AssociationType ezp.persistence.content.criteria.Criterions
	 * @AssociationMultiplicity 1
	 */
	public $parent_17;
}
?>