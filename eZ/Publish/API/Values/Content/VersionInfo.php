<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\Content\ContentInfo;

/**
 * This class holds version information data. It also contains the coresponding {@link Content} to
 * which the version belongs to.
 *
 * @property-read array $names returns an array with language code keys and name values
 * @property-read ContentInfo $contentInfo calls getContentInfo()
 * @property-read int $id the internal id of the version
 * @property-read int $versionNo the version number of this version (which only increments in scope of a single Content object)
 * @property-read DateTime $modifiedDate the last modified date of this version
 * @property-read DateTime $createdDate the creation date of this version
 * @property-read int $creatorId the user id of the user which created this version
 * @property-read int $status the status of this version. One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
 * @property-read string $initialLanguageCode the language code of the version. This value is used to flag a version as a translation to specific language
 * @property-read array $languageCodes a collection of all languages which exist in this version.
 */
abstract class VersionInfo extends ValueObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

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
     * @return ContentInfo
     */
    public abstract function getContentInfo();

    /**
     *
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode
     */
    public abstract function getName( $languageCode = null );

    /**
     * the last modified date of this version
     * 
     * @var DateTime
     */
    protected $modifiedDate;

    /**
     * Creator user ID.
     *
     * @var int
     */
    protected $creatorId;

    /**
     * @var DateTime
     */
    protected $createdDate;

    /**
     * One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
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
     * List of languages in this version
     *
     * Reflects which languages fields exists in for this version.
     *
     * @var string[]
     */
    protected $languageCodes = array();
}
