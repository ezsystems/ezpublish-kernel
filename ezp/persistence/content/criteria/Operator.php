<?php
/**
 * File containing the Operator
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\criteria;

/**
 * @package ezp.persistence.content.criteria
 */
class Operator 
{
	/**
	 * @AttributeType int
	 */
	const EQUALS = 0;
	/**
	 * @AttributeType int
	 */
	const gT = 1;
	/**
	 * @AttributeType int
	 */
	const lT = 2;
	/**
	 * @AttributeType int
	 */
	const LIKE = 3;
}
?>