<?php
/**
 * File containing the ContentServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\FieldHandler;

use \eZ\Publish\API\Repository\Tests\Stubs\FieldHandlerBase;
use \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;
use \eZ\Publish\API\Repository\Values\Content\Field;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Handles special fields
 */
class User extends FieldHandlerBase
{
    /**
     * Default values
     *
     * @var mixed
     */
    protected $defaultValues = array(
        'has_stored_login' => false,
    );

    /**
     * Repository
     *
     * @var RepositoryStub
     */
    protected $repository;

    /**
     * Construct from repository
     *
     * @param RepositoryStub $repository
     * @return void
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Handle a certain field
     *
     * @param FieldDefinition $fieldDefinition
     * @param Field $field
     * @param Content $content
     * @return void
     */
    public function handleCreate( FieldDefinition $fieldDefinition, Field $field, Content $content )
    {
        $field->setValue( array_merge( $this->defaultValues, $field->value ) );
    }
}

