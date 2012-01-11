<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\Content\VersionInfo;

/**
 * 
 * 5.x this class is used for reading and writing translation informations into the repository
 *
 */
class TranslationInfo extends ValueObject {
	/**
	 * the language code of the source language of the translation
	 * @var string
	 */
	public $sourceLanguageCode;
	
	/**
	 * the language code of the destination language of the translation
	 * @var string
	 */
	public $destinationLanguageCode;
	
	/**
	 * 
	 * the source version this translation is based on
	 * @var VersionInfo
	 */
	public $srcVersion;

	/**
	 * 
	 * the destination version this translation is placed in
	 * @var VersionInfo
	 */
	public $destinationVersion;
}