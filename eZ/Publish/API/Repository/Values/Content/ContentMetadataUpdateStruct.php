<?php
namespace eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 *
 * With this class data can be provided to update version independent fields of the content.
 * It is used in content update methods. At least one property in this class must be set.
 *
 */
class ContentMetaDataUpdateStruct extends ValueObject
{
    /**
     * If set this value changes the owner id of the content object
     *
     * @var mixed
     */
    public $ownerId;

    /**
     * if set this value overrides the publication date of the content. (Used in staging scenarios)
     *
     * @var \DateTime
     */
    public $publishedDate;

    /**
     * If set this value overrides the modification date. (Used for staging scenarios).
     *
     * @var \DateTime
     */
    public $modificationDate;

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

     /**
     * if set  main location.is changed to this value
     *
     * If the content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    public $mainLocationId;
}
