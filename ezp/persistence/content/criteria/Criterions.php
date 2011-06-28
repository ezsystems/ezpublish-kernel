<?php
/**
 * File containing the Criterions class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

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