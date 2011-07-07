<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content_type
 */

namespace ezp\persistence\content\type;

/**
 * @package ezp
 * @subpackage persistence_content_type
 */
class Type extends ContentTypeBase
{
	/**
	 */
	public $remoteId;
	/**
	 */
	public $urlAliasSchema;
	/**
	 */
	public $nameSchema;
	/**
	 */
	public $container;
	/**
	 */
	public $initialLanguage;
	/**
	 */
	public $unnamed_ContentTypeGroup_ = array();
	/**
	 */
	public $fieldDefinition = array();
}
?>
