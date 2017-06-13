<?php

/**
 * File containing the ContentTypeGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\SPI\Repository\Values\MultiLanguageName;
use eZ\Publish\SPI\Repository\Values\MultiLanguageDescription;

/**
 * This class represents a content type group value.
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read \DateTime $creationDate the date of the creation of this content type group
 * @property-read \DateTime $modificationDate the date of the last modification of this content type group
 * @property-read mixed $creatorId the user id of the creator of this content type group
 * @property-read mixed $modifierId the user id of the user which has last modified this content type group
 */
abstract class ContentTypeGroup extends ValueObject implements MultiLanguageName, MultiLanguageDescription
{
    /**
     * Primary key.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Readable string identifier of a group.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Created date (timestamp).
     *
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * Modified date (timestamp).
     *
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * Creator user id.
     *
     * @var mixed
     */
    protected $creatorId;

    /**
     * Modifier user id.
     *
     * @var mixed
     */
    protected $modifierId;
}
