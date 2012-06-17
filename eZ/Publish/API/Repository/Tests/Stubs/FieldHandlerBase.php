<?php
/**
 * File containing the ContentServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\Values\Content\Field;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Handles special fields
 */
abstract class FieldHandlerBase
{
    /**
     * Handle a certain field
     *
     * @param FieldDefinition $fieldDefinition
     * @param Field $field
     * @param Content $content
     * @return void
     */
    abstract public function handleCreate( FieldDefinition $fieldDefinition, Field $field, Content $content );
}

