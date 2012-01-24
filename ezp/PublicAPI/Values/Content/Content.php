<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ContentType\ContentType;
use ezp\PublicAPI\Values\Content\Location;
use ezp\PublicAPI\Values\ValueObject;



/**
 *
 * This class provides all version independent information of the content object.
 * @property-read $contentType calls {@link getContentType()}
 *
 */
abstract class Content extends ValueObject
{
    /**
     * The unique id of the content object
     * @var int
     */
    public $contentId;

    /**
     * true if there exists a published version 0 otherwise
     *
     * @var boolean Constant.
     */
    public $published;

    /**
     * the computed name (via name schema) in the main language of the content object
     * @var string
     */
    public $name;

    /**
     * The content type of this content object
     * @return ContentType
     */
    public abstract function getContentType();

    /**
     * the section to which the content is assigned
     * @var int
     */
    public $sectionId;

    /**
     * the owner of this content object
     *
     * @var int
     */
    public $ownerId;

    /**
     * Content modification date
     * @var int Unix timestamp
     */
    public $modified;

    /**
     * Content publication date
     * @var int Unix timestamp
     */
    public $publishedDate;

    /**
     * Current Version number is the version number of the published version or the version number of
     * a newly created draft (which is 1).
     *
     * @var int
     */
    public $currentVersionNo;

    /**
     * Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
     * @var boolean
     */
    public $alwaysAvailable;

    /**
     * Remote identifier used as a custom identifier for the object
     * @var string
     */
    public $remoteId;

    /**
     * The main language code of the content.
     * @var string
     */
    public $mainLanguageCode;

}
?>
