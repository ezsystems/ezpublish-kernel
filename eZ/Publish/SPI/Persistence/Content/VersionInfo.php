<?php
/**
 * File containing the VersionInfo class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class holds version information data
 */
class VersionInfo
{
    /**
 	 * Version ID.
 	 *
 	 * @var mixed
 	 */
	public $id;

	/**
  	 * Version number.
  	 *
  	 * In contrast to {@link $id}, this is the version number, which only
 	 * increments in scope of a single Content object.
 	 *
 	 * @var int
 	 */
	public $versionNo;

	/**
 	 * Content of the content this version belongs to.
 	 *
 	 * @var int $contentId
 	 */
	public $contentId;

	/**
 	 * Returns the names computed from the name schema in the available languages.
     * Eg. array( 'eng-GB' => "New Article" )
 	 *
 	 * @return string[]
 	 */
	public $names;

	/**
     * Creation date of this version, as a UNIX timestamp
 	 * @var int
 	 */
	public $creationDate;

	/**
 	 * Last modified date of this version, as a UNIX timestamp
 	 *
 	 * @var int
 	 */
	public $modificationDate;

	/**
 	 * Creator user ID.
 	 *
 	 * @var int
 	 */
	public $creatorId;


	/**
 	 * One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
 	 *
 	 * @var int
 	 */
	public $status;

	/**
 	 * In 4.x this is the language code which is used for labeling a translation.
 	 *
 	 * @var int
 	 */
	public $initialLanguageCode;

	/**
 	 * List of languages in this version
 	 * Reflects which languages fields exists in for this version.
 	 *
 	 * @var int[]
 	 */
	public $languageIds = array();
}
