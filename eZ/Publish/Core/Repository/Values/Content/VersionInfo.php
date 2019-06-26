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
    /** @var string[] */
    protected $names;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    protected $contentInfo;

    /**
     * The first matched name language among user provided prioritized languages.
     *
     * The first matched language among user provided prioritized languages on object retrieval, or null if none
     * provided (all languages) or on main fallback.
     *
     * @internal
     * @var string|null
     */
    protected $prioritizedNameLanguageCode;

    /**
     * {@inheritdoc}
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($languageCode = null)
    {
        if ($languageCode) {
            return isset($this->names[$languageCode]) ? $this->names[$languageCode] : null;
        }

        if ($this->prioritizedNameLanguageCode) {
            return $this->names[$this->prioritizedNameLanguageCode];
        } elseif (!empty($this->contentInfo->alwaysAvailable) && isset($this->names[$this->contentInfo->mainLanguageCode])) {
            return $this->names[$this->contentInfo->mainLanguageCode];
        }

        // Versioned name should always exists in initial language for a version so we use that as final fallback
        return $this->names[$this->initialLanguageCode];
    }
}
