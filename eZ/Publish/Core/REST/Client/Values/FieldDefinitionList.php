<?php
/**
 * File containing the FieldDefinitionList class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\Core\REST\Client\ContentTypeService;

/**
 * FieldDefinitionList
 */
class FieldDefinitionList
{
    /**
     * References to contained field references
     *
     * @var string[]
     */
    protected $fieldDefinitionReferences;

    /**
     * Content type service
     *
     * @var \eZ\Publish\Core\REST\Client\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param \eZ\Publish\Core\REST\Client\ContentTypeService $contentTypeService
     * @param string[] $fieldDefinitionReferences
     */
    public function __construct( ContentTypeService $contentTypeService, array $fieldDefinitionReferences )
    {
        $this->contentTypeService = $contentTypeService;
        $this->fieldDefinitionReferences = $fieldDefinitionReferences;
    }

    /**
     * Fetches and returns the field definitions contained in the list
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        $fieldDefinitions = array();
        foreach ( $this->fieldDefinitionReferences as $reference )
        {
            $fieldDefinitions[] = $this->contentTypeService->loadFieldDefinition( $reference );
        }
        return $fieldDefinitions;
    }
}
