<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\ContentInfo class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class provides all version independent information of the content object.
 *
 * @property-read mixed $id The unique id of the content object
 * @property-read mixed $contentTypeId The unique id of the content type object this content is an instance of
 * @property-read string $name the computed name (via name schema) in the main language of the content object
 * @property-read mixed $sectionId the section to which the content is assigned
 * @property-read int $currentVersionNo Current Version number is the version number of the published version or the version number of a newly created draft (which is 1).
 * @property-read boolean $published true if there exists a published version false otherwise
 * @property-read mixed $ownerId the user id of the owner of the content
 * @property-read \DateTime $modificationDate Content modification date
 * @property-read \DateTime $publishedDate date of the last publish operation
 * @property-read boolean $alwaysAvailable Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
 * @property-read string $remoteId a global unique id of the content object
 * @property-read string $mainLanguageCode The main language code of the content. If the available flag is set to true the content is shown in this language if the requested language does not exist.
 * @property-read mixed $mainLocationId Identifier of the main location.
 */
class ContentInfo extends ValueObject
{
    /**
     * The unique id of the content object
     * @var mixed
     */
    protected $id;

    /**
     * The content type id of the content.
     *
     * @var mixed
     */
    protected $contentTypeId;

    /**
     * the computed name (via name schema) in the main language of the content object
     * @var string
     */
    protected $name;

    /**
     * the section to which the content is assigned
     * @var mixed
     */
    protected $sectionId;

    /**
     * Current Version number is the version number of the published version or the version number of
     * a newly created draft (which is 1).
     *
     * @var int
     */
    protected $currentVersionNo;

    /**
     * true if there exists a published version 0 otherwise
     *
     * @var boolean Constant.
     */
    protected $published;

    /**
     * the owner of this content object
     *
     * @var mixed
     */
    protected $ownerId;

    /**
     * Content modification date
     *
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * Content publication date
     *
     * @var \DateTime
     */
    protected $publishedDate;

    /**
     * Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
     * @var boolean
     */
    protected $alwaysAvailable;

    /**
     * Remote identifier used as a custom identifier for the object
     * @var string
     */
    protected $remoteId;

    /**
     * The main language code of the content.
     * @var string
     */
    protected $mainLanguageCode;

     /**
     * Identifier of the main location.
     *
     * If the content object has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    protected $mainLocationId;
}
