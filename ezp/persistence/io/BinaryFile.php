<?php
/**
 * File containing the BinaryFile class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\io;

/**
 * @package ezp.persistence.io
 */
class BinaryFile 
{
	/**
	 */
	private $fileName;
	/**
	 */
	private $originalFilename;
	/**
	 */
	private $contentType;
	/**
	 */
	private $version;
	/**
	 */
	public $contains;
}
?>
