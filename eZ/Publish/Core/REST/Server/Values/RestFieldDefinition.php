<?php
/**
 * File containing the RestFieldDefinition class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * RestFieldDefinition view model
 */
class RestFieldDefinition
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
     * @var FieldDefinition
     */
    public $fieldDefinition;

    /**
     * Construct
     *
     * @param ContentType $contentType
     * @param FieldDefinition $fieldDefinition
     */
    public function __construct( ContentType $contentType, FieldDefinition $fieldDefinition )
    {
        $this->contentType = $contentType;
        $this->fieldDefinition = $fieldDefinition;
    }
}
