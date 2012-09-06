<?php
/**
 * File containing the FieldDefinitionList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * FieldDefinition list view model
 */
class FieldDefinitionList
{
    /**
     * ID of the content object the fieldDefinitions belong to
     *
     * @var mixed
     */
    public $contentTypeId;

    /**
     * FieldDefinitions
     *
     * @var array
     */
    public $fieldDefinitions;

    /**
     * Construct
     *
     * @param array $fieldDefinitions
     */
    public function __construct( $contentTypeId, array $fieldDefinitions )
    {
        $this->contentTypeId = $contentTypeId;
        $this->fieldDefinitions = $fieldDefinitions;
    }
}
