<?php
namespace ezp\persistence\io;
/**
 * @access public
 * @package ezp.persistence.io
 */
interface BinaryFileStorageInterface 
{

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function storeFile($fileIdentifier);

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return FileReference
	 * @ParamType fileIdentifier string
	 * @ReturnType FileReference
	 */
	public function getFile($fileIdentifier);

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return FileChunk
	 * @ParamType fileIdentifier string
	 * @ReturnType FileChunk
	 */
	public function streamFile($fileIdentifier);

	/**
	 * @access public
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function exists($fileIdentifier);

	/**
	 * @access public
	 */
	public function authenticate();
}
?>