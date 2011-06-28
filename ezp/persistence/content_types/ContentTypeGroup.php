<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content_types;

/**
 * @package ezp.persistence.content_types
 */
class ContentTypeGroup extends ContentTypeBase 
{
	/**
	 * @AssociationType ezp.persistence.content_types.ContentType
	 * @AssociationMultiplicity 0..*
	 * @AssociationKind Aggregation
	 */
	public $unnamed_ContentType_ = array();
}
?>