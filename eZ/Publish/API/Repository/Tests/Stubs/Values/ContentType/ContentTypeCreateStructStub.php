<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeCreateStructStub class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

class ContentTypeCreateStructStub extends ContentTypeCreateStruct
{
    protected $fieldDefinitions = array();

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * Adds a new field definition
     *
     * @param FieldDefinitionCreate $fieldDef
     */
    public function addFieldDefinition( FieldDefinitionCreateStruct $fieldDef )
    {
        $this->fieldDefinitions[] = $fieldDef;
    }
}
