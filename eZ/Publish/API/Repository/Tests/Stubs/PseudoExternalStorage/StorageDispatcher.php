<?php
/**
 * File containing the ContentServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage;

use eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Dispatcher for PseudoExternalStorage implementations.
 *
 * Dispatches the called actions to a registered internal pseudo storage.
 */
class StorageDispatcher extends PseudoExternalStorage
{
    /**
     * Array of pseudo storages, indexed by their field type
     *
     * @var \eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage[]
     */
    protected $pseudoStorages = array();

    /**
     * Construct dispatcher from given $pseudoStorages
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage[] $pseudoStorages
     */
    public function __construct( array $pseudoStorages = array() )
    {
        $this->pseudoStorages = $pseudoStorages;
    }

    /**
     * Handle creation of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    public function handleCreate( FieldDefinition $fieldDefinition, Field $field, Content $content )
    {
        if ( !isset( $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier] ) )
        {
            return false;
        }

        $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier]->handleCreate( $fieldDefinition, $field, $content );
    }

    /**
     * Handle updating of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    public function handleUpdate( FieldDefinition $fieldDefinition, Field $field, Content $content )
    {
        if ( !isset( $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier] ) )
        {
            return false;
        }

        $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier]->handleUpdate( $fieldDefinition, $field, $content );
    }

    /**
     * Handle loading of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    public function handleLoad( FieldDefinition $fieldDefinition, Field $field, Content $content )
    {
        if ( !isset( $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier] ) )
        {
            return false;
        }

        $this->pseudoStorages[$fieldDefinition->fieldTypeIdentifier]->handleLoad( $fieldDefinition, $field, $content );
    }
}

