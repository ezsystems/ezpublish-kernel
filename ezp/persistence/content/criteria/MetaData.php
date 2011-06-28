<?php
/**
 * File containing the MetaData
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\criteria;

/**
 * @package ezp.persistence.content.criteria
 */
class MetaData 
{
	/**
	 * @AttributeType int
	 */
	const STATE = 0;
	/**
	 * @AttributeType int
	 */
	const OWNER = 1;
	/**
	 * @AttributeType int
	 */
	const MODIFIED = 2;
	/**
	 * @AttributeType int
	 */
	const CREATED = 3;
}
?>