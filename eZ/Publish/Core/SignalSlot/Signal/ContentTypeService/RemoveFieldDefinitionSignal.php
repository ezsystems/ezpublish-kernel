<?php
/**
 * RemoveFieldDefinitionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RemoveFieldDefinitionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class RemoveFieldDefinitionSignal extends Signal
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
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function __construct( $contentTypeDraft, $fieldDefinition )
    {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinition = $fieldDefinition;
    }
}

