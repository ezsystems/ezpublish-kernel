<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\Content\Content;

/**
 * This class holds version information data. It also contains the coresponding {@link Content} to
 * which the version belongs to.
 *
 * @property-read array $names returns an array with language code keys and name values
 * @property-read Content $content calls getContent()
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
     * @return Content
     */
    public abstract function getContent();

    /**
     *
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode
     */
    public abstract function getName( $languageCode = null );

    /**
     * @var int
     */
    public $modified;

    /**
     * Creator user ID.
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * @var int
     */
    public $created;

    /**
     * One of VersionInfo::STATUS_DRAFT, VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED
     *
     * @var int Constant.
     */
    public $status;

    /**
     * In 4.x this is the language code which is used for labeling a translation.
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * List of languages in this version
     *
     * Reflects which languages fields exists in for this version.
     *
     * @var string[]
     */
    public $languageCodes = array();
}
