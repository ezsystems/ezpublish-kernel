<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use DateTime;

/**
 * This class provides all version independent information of the content object.
 * 
 * @property-read eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType calls {@link getContentType()}
 * @property-read int $contentId The unique id of the content object
 * @property-read string $name the computed name (via name schema) in the main language of the content object
 * @property-read int $sectionId the section to which the content is assigned
 * @property-read int $currentVersionNo Current Version number is the version number of the published version or the version number of a newly created draft (which is 1).
 * @property-read boolean $published true if there exists a published version false otherwise
 * @property-read int $ownerId the user id of the owner of the content
 * @property-read DateTime $modifiedDate Content modification date
 * @property-read DateTime $publishedDate date of the last publish operation
 * @property-read boolean $alwaysAvailable Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
 * @property-read string $remoteId a global unique id of the content object
 * @property-read string $mainLanguageCode The main language code of the content. If the available flag is set to true the content is shown in this language if the requested language does not exist.
 */
abstract class ContentInfo extends ValueObject
{
    /**
     * The unique id of the content object
     * @var int
     */
    protected $contentId;

    /**
     * the computed name (via name schema) in the main language of the content object
     * @var string
     */
    protected $name;

    /**
     * The content type of this content object
     * @return ContentType
     */
    public abstract function getContentType();

    /**
     * the section to which the content is assigned
     * @var int
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
     * @var int
     */
    protected $ownerId;

    /**
     * Content modification date
     * @var DateTime
     */
    protected $modifiedDate;

    /**
     * Content protectedation date
     * @var DateTime
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
}
