<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * @var mixed|null
     */
    public $ownerId;

    /**
     * If set this value overrides the publication date of the content. (Used in staging scenarios).
     *
     * @var \DateTime|null
     */
    public $publishedDate;

    /**
     * If set this value overrides the modification date. (Used for staging scenarios).
     *
     * @var \DateTime|null
     */
    public $modificationDate;

    /**
     * If set the main language of the content object is changed.
     *
     * @var string|null
     */
    public $mainLanguageCode;

    /**
     * If set this value changes the always available flag.
     *
     * @var bool|null
     */
    public $alwaysAvailable;

    /**
     * If set this value changes the remoteId.
     *
     * Needs to be a unique Content->remoteId string value.
     *
     * @var string|null
     */
    public $remoteId;

    /**
     * If set  main location is changed to this value.
     *
     * If the content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed|null
     */
    public $mainLocationId;

    /**
     * If set, will change the content's "always-available" name.
     *
     * @var string
     */
    public $name;
}
