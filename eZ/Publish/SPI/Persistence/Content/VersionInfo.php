<?php

/**
 * File containing the VersionInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class holds version information data.
 */
class VersionInfo extends ValueObject
{
    /**
     * Version status constants.
     *
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_PENDING = 2;
    const STATUS_ARCHIVED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_INTERNAL_DRAFT = 5;
    const STATUS_REPEAT = 6;
    const STATUS_QUEUED = 7;

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
     * ContentInfo of the content this VersionInfo belongs to.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * Returns the names computed from the name schema in the available languages.
     * Eg. array( 'eng-GB' => "New Article" ).
     *
     * @return string[]
     */
    public $names;

    /**
     * Creation date of this version, as a UNIX timestamp.
     *
     * @var int
     */
    public $creationDate;

    /**
     * Last modified date of this version, as a UNIX timestamp.
     *
     * @var int
     */
    public $modificationDate;

    /**
     * Creator user ID.
     *
     * Creator of the version, in the search API this is referred to as the modifier of the published content.
     *
     * @var int
     */
    public $creatorId;

    /**
     * One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED.
     *
     * @var int
     */
    public $status;

    /**
     * In 4.x this is the language code which is used for labeling a translation.
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * List of languages in this version.
     *
     * Reflects which languages fields exists in for this version.
     *
     * @var string[]
     */
    public $languageCodes = array();
}
