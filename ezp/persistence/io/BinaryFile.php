<?php
namespace ezp\persistence\io;
/**
 * @package ezp.persistence.io
 */
class BinaryFile 
{
	/**
	 * @AttributeType string
	 */
	private $fileName;
	/**
	 * @AttributeType string
	 */
	private $originalFilename;
	/**
	 * @AttributeType string
	 */
	private $contentType;
	/**
	 * @AttributeType int
	 */
	private $version;
	/**
	 * @AssociationType ezp.persistence.io.FileSystemStorage
	 */
	public $contains;
}
?>