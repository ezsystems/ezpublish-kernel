<?php
/**
 * File containing the ContentField class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content\values;

/**
 * @package ezp.persistence.content.values
 */
class ContentField 
{
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AttributeType string
	 */
	public $type;
	public $value;
	/**
	 * @AttributeType string
	 */
	public $language;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentVersion
	 */
	public $unnamed_ContentVersion_;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentCreateStruct
	 */
	public $unnamed_ContentCreateStruct_;
	/**
	 * @AssociationType ezp.persistence.content.values.ContentUpdateStruct
	 */
	public $unnamed_ContentUpdateStruct_;
}
?>