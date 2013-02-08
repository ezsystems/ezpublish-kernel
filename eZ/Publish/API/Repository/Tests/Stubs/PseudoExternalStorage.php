<?php
/**
 * File containing the ContentServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Base class for faking external storage activity.
 *
 * Implementations of this base class are used in order to fake the behavior of
 * externals storages (which are normally implemented on basis of the
 * SPI\FieldType\FieldStorage and used by the SPI\Persistence implementations) in
 * the integration test suite for the public API.
 *
 * Namely, for each field type that has an external storage, an implementation
 * of this class has to exist.
 */
abstract class PseudoExternalStorage
{
    /**
     * Handle creation of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    abstract public function handleCreate( FieldDefinition $fieldDefinition, Field $field, Content $content );

    /**
     * Handle updating of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    abstract public function handleUpdate( FieldDefinition $fieldDefinition, Field $field, Content $content );

    /**
     * Handle loading of the given $field.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    abstract public function handleLoad( FieldDefinition $fieldDefinition, Field $field, Content $content );
}

