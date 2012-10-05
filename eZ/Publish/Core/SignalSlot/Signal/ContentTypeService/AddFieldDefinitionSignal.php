<?php
/**
 * AddFieldDefinitionSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AddFieldDefinitionSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class AddFieldDefinitionSignal extends Signal
{
    /**
     * ContentTypeDraft
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public $contentTypeDraft;

    /**
     * FieldDefinitionCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public $fieldDefinitionCreateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function __construct( $contentTypeDraft, $fieldDefinitionCreateStruct )
    {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->fieldDefinitionCreateStruct = $fieldDefinitionCreateStruct;
    }
}

