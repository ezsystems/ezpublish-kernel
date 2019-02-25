<?php

/**
 * File containing the ContentInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class provides all version independent information of the content object.
 * It is similar to {@link \eZ\Publish\API\Repository\Values\Content\ContentInfo}, but for the persistence layer.
 * Thus it only contains raw data.
 */
class ContentInfo extends ValueObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_TRASHED = 2;

    /** @deprecated Use ContentInfo::STATUS_TRASHED */
    const STATUS_ARCHIVED = self::STATUS_TRASHED;

    /**
     * Content's unique ID.
     *
     * @var mixed
     */
    public $id;

    /**
     * Computed name (via name schema) in the main language.
     *
     * @var string
     */
    public $name;

    /**
     * Content type Id.
     *
     * @var int
     */
    public $contentTypeId;

    /**
     * Section id the content is assigned to.
     *
     * @var int
     */
    public $sectionId;

    /**
     * Version number of the current published version.
     * If the content is not published yet (newly created draft), will be 1.
     *
     * @var int
     */
    public $currentVersionNo;

    /**
     * @deprecated Use SPI\ContentInfo::$status (with value ContentInfo::STATUS_PUBLISHED)
     *
     * Flag indicating if content is currently published.
     *
     * @var bool
     */
    public $isPublished;

    /**
     * Content owner's id.
     *
     * @var int
     */
    public $ownerId;

    /**
     * Content modification date, as a UNIX timestamp.
     *
     * @var int
     */
    public $modificationDate;

    /**
     * Content publication date, as a UNIX timestamp.
     *
     * @var int
     */
    public $publicationDate;

    /**
     * Indicates if the content is shown in the main language if its not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;

    /**
     * Remote identifier used as a custom identifier for the object.
     *
     * @var string
     */
    public $remoteId;

    /**
     * The main language code of the content.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Identifier of the main location.
     *
     * If the content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    public $mainLocationId;

    /**
     * Status of the content.
     *
     * Replaces deprecated SPI\ContentInfo::$isPublished.
     *
     * @var int
     */
    public $status;

    /**
     * Flag indicating if content is currently hidden.
     *
     * @var bool
     */
    public $isHidden = false;
}
