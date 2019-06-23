<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

/**
 * this class is used for creating content types.
 *
 * @property \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[] $fieldDefinitions the collection of field definitions
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class ContentTypeCreateStruct extends APIContentTypeCreateStruct
{
    /**
     * Holds the collection of field definitions.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[]
     */
    public $fieldDefinitions = [];

    /**
     * Adds a new field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDef
     */
    public function addFieldDefinition(FieldDefinitionCreateStruct $fieldDef)
    {
        $this->fieldDefinitions[] = $fieldDef;
    }
}
