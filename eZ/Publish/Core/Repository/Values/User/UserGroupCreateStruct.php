<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct as APIUserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * This class is used to create a new user group in the repository.
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class UserGroupCreateStruct extends APIUserGroupCreateStruct
{
    /**
     * The list of fields added to the user group.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public $fields = [];

    /**
     * Adds a field to the field collection.
     *
     * This method could also be implemented by a magic setter so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the corresponding
     *                     field type or an instance of a Value class provided by the field type
     * @param string|null $language If not given on a translatable field the initial language is used
     */
    public function setField($fieldDefIdentifier, $value, $language = null)
    {
        if (!isset($language)) {
            $language = $this->mainLanguageCode;
        }

        $this->fields[] = new Field(
            [
                'fieldDefIdentifier' => $fieldDefIdentifier,
                'value' => $value,
                'languageCode' => $language,
            ]
        );
    }
}
