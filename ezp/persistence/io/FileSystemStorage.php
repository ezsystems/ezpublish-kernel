<?php
namespace ezp\persistence\io;
/**
 * @access public
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
	 * @access public
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function storeFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return FileReference
	 * @ParamType fileIdentifier string
	 * @ReturnType FileReference
	 */
	public function getFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return FileChunk
	 * @ParamType fileIdentifier string
	 * @ReturnType FileChunk
	 */
	public function streamFile($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function exists($fileIdentifier) {
		// Not yet implemented
	}

	/**
	 * @access public
	 */
	public function authenticate() {
		// Not yet implemented
	}
}
?>