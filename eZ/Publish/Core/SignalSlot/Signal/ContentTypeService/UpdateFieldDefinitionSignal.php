<?php
/**
 * UpdateFieldDefinitionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateFieldDefinitionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class UpdateFieldDefinitionSignal extends Signal
{
    /**
     * ContentTypeDraft
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public $contentTypeDraft;

    /**
     * FieldDefinition
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public $fieldDefinition;

    /**
     * FieldDefinitionUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public $fieldDefinitionUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function __construct( $contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct )
    {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinition = $fieldDefinition;
        $this->fieldDefinitionUpdateStruct = $fieldDefinitionUpdateStruct;
    }
}

