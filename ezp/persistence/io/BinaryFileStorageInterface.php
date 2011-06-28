<?php
namespace ezp\persistence\io;
/**
 * @package ezp.persistence.io
 */
interface BinaryFileStorageInterface 
{

	/**
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function storeFile($fileIdentifier);

	/**
	 * @param string fileIdentifier
	 * @return FileReference
	 * @ParamType fileIdentifier string
	 * @ReturnType FileReference
	 */
	public function getFile($fileIdentifier);

	/**
	 * @param string fileIdentifier
	 * @return FileChunk
	 * @ParamType fileIdentifier string
	 * @ReturnType FileChunk
	 */
	public function streamFile($fileIdentifier);

	/**
	 * @param string fileIdentifier
	 * @return boolean
	 * @ParamType fileIdentifier string
	 * @ReturnType boolean
	 */
	public function exists($fileIdentifier);

	/**
	 */
	public function authenticate();
}
?>