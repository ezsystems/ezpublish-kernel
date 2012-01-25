<?php
namespace ezp\PublicAPI\Values\Content;
use ezp\PublicAPI\Values\ValueObject;

/**
 *
 * With this class data can be provided to update version independent fields of the content.
 * It is used in content update methods.
 *
 */
class ContentUpdateStruct extends ValueObject
{
    /**
     * If set this value changes the owner id of the content object
     *
     * @var integer
     */
    public $ownerId = null;

    /**
     * if set this value overrides the publication date of the content. (Used in staging scenarios)
     *
     * @var integer Unix timestamp
     */
    public $published = null;

    /**
     * If set this value overrides the modification date. (Used for staging scenarios).
     *
     * @var integer Unix timestamp
     */
    public $modified;

    /**
     * if set the main language of the content object is changed.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * If set this value changes the always available flag
     *
     * @var boolean
     */
    public $alwaysAvailable;

    /**
     * if set this value  changes the remoteId
     *
     * @var string
     */
    public $remoteId;
}
