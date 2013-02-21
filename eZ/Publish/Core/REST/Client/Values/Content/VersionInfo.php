<?php
/**
 * File containing the VersionInfo class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use eZ\Publish\Core\REST\Client\ContentService;

/**
 * This class holds version information data. It also contains the corresponding {@link Content} to
 * which the version belongs to.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo calls getContentInfo()
 * @property-read mixed $id the internal id of the version
 * @property-read int $versionNo the version number of this version (which only increments in scope of a single Content object)
 * @property-read \DateTime $modificationDate the last modified date of this version
 * @property-read \DateTime $creationDate the creation date of this version
 * @property-read mixed $creatorId the user id of the user which created this version
 * @property-read int $status the status of this version. One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
 * @property-read string $initialLanguageCode the language code of the version. This value is used to flag a version as a translation to specific language
 * @property-read array $languageCodes a collection of all languages which exist in this version.
 */
class VersionInfo extends \eZ\Publish\API\Repository\Values\Content\VersionInfo
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentService;

    /**
     * @var string
     */
    protected $contentInfoId;

    /**
     * @var string[]
     */
    protected $names;

    public function __construct( ContentService $contentService, array $data = array() )
    {
        parent::__construct( $data );

        $this->contentService = $contentService;
    }

    /**
     * Content of the content this version belongs to.
     *
     * @return ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentService->loadContentInfo( $this->contentInfoId );
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
    public function getName( $languageCode = null )
    {
        if ( $languageCode === null )
        {
            $languageCode = $this->initialLanguageCode;
        }
        return $this->names[$languageCode];
    }

    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'contentInfo':
                return $this->getContentInfo();
        }
        return parent::__get( $propertyName );
    }

    public function __isset( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'contentInfo':
                return true;
        }
        return parent::__isset( $propertyName );
    }
}
