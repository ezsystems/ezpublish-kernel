<?php
/**
 * File containing the FileSystemStorage class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\io;

/**
 * @package ezp.persistence.io
 */
class FileSystemStorage implements BinaryFileStorageInterface 
{
	/**
	 * @AssociationType ezp.persistence.io.BinaryFile
	 * @AssociationKind Aggregation
	 */
	public $contains;

	/**
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function storeFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @param string fileIdentifier
	 * @return FileReference
	 * @ParamType fileIdentifier string
	 * @ReturnType FileReference
	 */
	public function getFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @param string fileIdentifier
	 * @return FileChunk
	 * @ParamType fileIdentifier string
	 * @ReturnType FileChunk
	 */
	public function streamFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function exists($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 */
	public function authenticate() {
		// Not yet implemented
	}
}
?>