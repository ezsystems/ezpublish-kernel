<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\VersionInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class holds version information data. It also contains the corresponding {@link Content} to
 * which the version belongs to.
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo calls getContentInfo()
 * @property-read mixed $id the internal id of the version
 * @property-read int $versionNo the version number of this version (which only increments in scope of a single Content object)
 * @property-read \DateTime $modificationDate the last modified date of this version
 * @property-read \DateTime $creationDate the creation date of this version
 * @property-read mixed $creatorId the user id of the user which created this version
 * @property-read int $status the status of this version. One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
 * @property-read string $initialLanguageCode the language code of the version. This value is used to flag a version as a translation to specific language
 * @property-read string[] $languageCodes a collection of all languages which exist in this version.
 */
abstract class VersionInfo extends ValueObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 3;

    /**
     * Version ID.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Version number.
     *
     * In contrast to {@link $id}, this is the version number, which only
     * increments in scope of a single Content object.
     *
     * @var int
     */
    protected $versionNo;

    /**
     * Content of the content this version belongs to.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    abstract public function getContentInfo();

    /**
     * Returns the names computed from the name schema in the available languages.
     *
     * @return string[]
     */
    abstract public function getNames();

    /**
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode
     *
     * @return string
     */
    abstract public function getName($languageCode = null);

    /**
     * the last modified date of this version.
     *
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * Creator user ID.
     *
     * Creator of the version, in the search API this is referred to as the modifier of the published content.
     *
     * @var mixed
     */
    protected $creatorId;

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED.
     *
     * @var int Constant.
     */
    protected $status;

    /**
     * In 4.x this is the language code which is used for labeling a translation.
     *
     * @var string
     */
    protected $initialLanguageCode;

    /**
     * List of languages in this version.
     *
     * Reflects which languages fields exists in for this version.
     *
     * @var string[]
     */
    protected $languageCodes = array();
}
