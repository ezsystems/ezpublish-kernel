<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for creating a new content object
 */
abstract class ContentCreateStruct extends ValueObject
{
    /**
     * The content type for which the new content is created
     *
     * @var ContentType
     */
    public $contentType;

    /**
     * The section the content is assigned to.
     * If not set the section of the parent is used or a default section.
     *
     * @var int
     */
    public $sectionId = null;

    /**
     * The owner of the content. If not given the current authenticated user is set as owner.
     *
     * @var int
     */
    public $ownerId = null;

    /**
     * Indicates if the content object is shown in the mainlanguage if its not present in an other requested language
     *
     * @var boolean
     */
    public $alwaysAvailable = true;

    /**
     * Remote identifier used as a custom identifier for the object
     *
     * @var string
     */
    public $remoteId = null;

    /**
     * the main language code for the content. This language will also
     * be used for as initial language for the first created version.
     * It is also used as default language for added fields.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Modification date. If not given the current timestamp is used.
     *
     * @var \DateTime
     */
    public $modificationDate;

    /**
     * Adds a field to the field collection.
     *
     * This method could also be implemented by a magic setter so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     *
     * @param mixed $value Either a plain value which is understandable by the corresponding
     *                     field type or an instance of a Value class provided by the field type
     *
     * @param string|null $language If not given on a translatable field the initial language is used
     */
    abstract public function setField( $fieldDefIdentifier, $value, $language = null );
}
