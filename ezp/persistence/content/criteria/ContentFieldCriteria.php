<?php
/**
 * File containing the ContentFieldCriteria class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\criteria;

/**
 * @package ezp.persistence.content.criteria
 */
class ContentFieldCriteria extends Criteria 
{
	/**
	 * @AttributeType ezp.persistence.content.criteria.Operator
	 */
	public $operator;
	/**
	 * @AttributeType string
	 */
	public $fieldIdentifier;
	public $value;
}
?>