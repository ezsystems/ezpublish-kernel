<?php
/**
 * File containing the FieldDefinitionList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * FieldDefinition list view model
 */
class FieldDefinitionList
{
    /**
     * ContentType the field definitions belong to
     *
     * @var ContentType
     */
    public $contentType;

    /**
     * FieldDefinitions
     *
     * @var array
     */
    public $fieldDefinitions;

    /**
     * Construct
     *
     * @param ContentType $contentType
     * @param array $fieldDefinitions
     */
    public function __construct( ContentType $contentType, array $fieldDefinitions )
    {
        $this->contentType = $contentType;
        $this->fieldDefinitions = $fieldDefinitions;
    }
}
