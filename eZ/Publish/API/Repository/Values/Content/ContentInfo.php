<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class provides all version independent information of the Content object.
 *
 * @property-read int|string $id The unique id of the Content object
 * @property-read int|string $contentTypeId The unique id of the Content Type object the Content object is an instance of
 * @property-read string $name the computed name (via name schema) in the main language of the Content object
 * @property-read int|string $sectionId the section to which the Content object is assigned
 * @property-read int|string $currentVersionNo Current Version number is the version number of the published version or the version number of a newly created draft (which is 1).
 * @property-read bool $published true if there exists a published version false otherwise
 * @property-read int|string $ownerId the user id of the owner of the Content object
 * @property-read \DateTime $modificationDate Content object modification date
 * @property-read \DateTime $publishedDate date of the first publish
 * @property-read bool $alwaysAvailable Indicates if the Content object is shown in the mainlanguage if its not present in an other requested language
 * @property-read string $remoteId a global unique id of the Content object
 * @property-read string $mainLanguageCode The main language code of the Content object. If the available flag is set to true the Content is shown in this language if the requested language does not exist.
 * @property-read int|string|null $mainLocationId Identifier of the main location. null when not published
 */
class ContentInfo extends ValueObject
{
    /**
     * The unique id of the Content object.
     *
     * @var int|string
     */
    protected $id;

    /**
     * The Content Type id of the Content object.
     *
     * @var int|string
     */
    protected $contentTypeId;

    /**
     * The computed name (via name schema) in the main language of the Content object.
     *
     * For names in other languages then main see {@see \eZ\Publish\API\Repository\Values\Content\VersionInfo}
     *
     * @var string
     */
    protected $name;

    /**
     * The section to which the Content object is assigned.
     *
     * @var int|string
     */
    protected $sectionId;

    /**
     * Current Version number is the version number of the published version or the version number of
     * a newly created draft (which is 1).
     *
     * @var int|string
     */
    protected $currentVersionNo;

    /**
     * True if there exists a published version, false otherwise.
     *
     * @var bool Constant.
     */
    protected $published;

    /**
     * The owner of the Content object.
     *
     * @var int|string
     */
    protected $ownerId;

    /**
     * Content modification date.
     *
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * Content publication date.
     *
     * @var \DateTime
     */
    protected $publishedDate;

    /**
     * Indicates if the Content object is shown in the mainlanguage if its not present in an other requested language.
     *
     * @var bool
     */
    protected $alwaysAvailable;

    /**
     * Remote identifier used as a custom identifier for the object.
     *
     * @var string
     */
    protected $remoteId;

    /**
     * The main language code of the Content object.
     *
     * @var string
     */
    protected $mainLanguageCode;

    /**
     * Identifier of the main location.
     *
     * If the Content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var int|string|null
     */
    protected $mainLocationId;
}
