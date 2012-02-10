<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserCreateStruct as APIUserCreateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;

/**
 * This class is used to create a new user in the repository
 */
class UserCreateStruct extends APIUserCreateStruct
{
    /**
     * Instance of ContentCreateStruct value object that this user create struct encapsulates
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    protected $contentCreateStruct;

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
    public function setField( $fieldDefIdentifier, $value, $language = null )
    {
        $this->contentCreateStruct->setField( $fieldDefIdentifier, $value, $language );
    }
}
