<?php
/**
 * NewFieldDefinitionCreateStructSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * NewFieldDefinitionCreateStructSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class NewFieldDefinitionCreateStructSignal extends Signal
{
    /**
     * Identifier
     *
     * @var mixed
     */
    public $identifier;

    /**
     * FieldTypeIdentifier
     *
     * @var mixed
     */
    public $fieldTypeIdentifier;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $identifier
     * @param mixed $fieldTypeIdentifier
     */
    public function __construct( $identifier, $fieldTypeIdentifier )
    {
        $this->identifier = $identifier;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }
}

