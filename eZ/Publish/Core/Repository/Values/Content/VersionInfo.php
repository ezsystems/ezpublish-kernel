<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\VersionInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;

/**
 * This class holds version information data. It also contains the corresponding {@link Content} to
 * which the version belongs to.
 *
 * @property-read string[] $names returns an array with language code keys and name values
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo calls getContentInfo()
 * @property-read int $id the internal id of the version
 * @property-read int $versionNo the version number of this version (which only increments in scope of a single Content object)
 * @property-read \DateTime $modifiedDate the last modified date of this version
 * @property-read \DateTime $createdDate the creation date of this version
 * @property-read int $creatorId the user id of the user which created this version
 * @property-read int $status the status of this version. One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
 * @property-read string $initialLanguageCode the language code of the version. This value is used to flag a version as a translation to specific language
 * @property-read string[] $languageCodes a collection of all languages which exist in this version.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class VersionInfo extends APIVersionInfo
{
    /**
     * @var string[]
     */
    protected $names;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * Content of the content this version belongs to.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * Returns the names computed from the name schema in the available languages.
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getName($languageCode = null)
    {
        if (!isset($languageCode)) {
            $languageCode = $this->initialLanguageCode;
        }

        if (isset($this->names[$languageCode])) {
            return $this->names[$languageCode];
        }

        return null;
    }
}
