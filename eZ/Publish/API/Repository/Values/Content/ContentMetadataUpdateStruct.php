<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * With this class data can be provided to update version independent fields of the content.
 * It is used in content update methods. At least one property in this class must be set.
 */
class ContentMetadataUpdateStruct extends ValueObject
{
    /**
     * If set this value changes the owner id of the content object.
     *
     * @var mixed
     */
    public $ownerId;

    /**
     * If set this value overrides the publication date of the content. (Used in staging scenarios)
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
     * If set the main language of the content object is changed.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * If set this value changes the always available flag.
     *
     * @var boolean
     */
    public $alwaysAvailable;

    /**
     * If set this value changes the remoteId.
     *
     * Needs to be a unique Content->remoteId string value.
     *
     * @var string
     */
    public $remoteId;

     /**
     * If set  main location is changed to this value.
     *
     * If the content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    public $mainLocationId;
}
