<?php
/**
 * File containing the User class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage;

use eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage;

use eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\Content\FieldStub;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\User\Value;

/**
 * Handles external storage of the "user" field type.
 */
class User extends PseudoExternalStorage
{
    /**
     * Default values of the user field.
     *
     * @var array
     */
    protected $defaultValues = array(
        'hasStoredLogin'   => false,
        'contentId'        => null,
        'login'            => null,
        'email'            => null,
        'passwordHash'     => null,
        'passwordHashType' => null,
        'enabled'        => false,
        'maxLogin'         => null,
    );

    /**
     * Editable properties
     *
     * @var array
     */
    protected $editable = array();

    /**
     * Storage for field data
     *
     * @var array
     */
    protected $fieldData = array();

    /**
     * Repository implementation
     *
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    protected $repository;

    /**
     * Construct from repository
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Returns a mocked content service for internal use.
     *
     * This mock object is only used for the $this->getContentService() call in
     * the user fixture file. The mock avoids loading the corresponding content
     * objects, in order to avoid the circular reference.
     *
     * @return \eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage\User\ContentServiceMock
     */
    protected function getContentService()
    {
        return new User\ContentServiceMock();
    }

    /**
     * Returns the User objects from the fixture
     *
     * This method uses a quite some black magic in order to fetch the user
     * fixture without creating a circular reference.
     *
     * @return \eZ\Publish\API\Repository\Tests\Stubs\Values\User\User
     */
    protected function getUserData()
    {
        // Retrieve user service without having it initialized explicitly
        $userServiceProperty = new \ReflectionProperty( $this->repository, 'userService' );
        $userServiceProperty->setAccessible( true );
        if ( $service = $userServiceProperty->getValue( $this->repository ) )
        {
            // If the service has been initialized, retrieve the fixture data
            // from its property
            $usersProperty = new \ReflectionProperty( $service, 'users' );
            $usersProperty->setAccessible( true );
            return $usersProperty->getValue( $service );
        }

        // Otherwise retrieve the fixture manually
        $fixtureDirProperty = new \ReflectionProperty( $this->repository, 'fixtureDir' );
        $fixtureDirProperty->setAccessible( true );
        $fixtureDir = $fixtureDirProperty->getValue( $this->repository );

        $data = include $fixtureDir . '/UserFixture.php';
        return reset( $data );
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
        $this->handleUpdate( $fieldDefinition, $field, $content );
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
        $storage = new Value();

        foreach ( $this->defaultValues as $key => $default )
        {
            if ( !empty( $field->value->$key ) &&
                 isset( $this->editable[$key] ) )
            {
                $storage->$key = $field->value->$key;
            }
            else
            {
                $storage->$key = $default;
            }
        }

        $this->fieldData[$content->id] = $storage;
        $this->handleLoad( $fieldDefinition, $field, $content );
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
        $userData = $this->getUserData();
        if ( !isset( $userData[$content->id] ) ||
             !isset( $this->fieldData[$content->id] ) )
        {
            if ( $field instanceof FieldStub )
            {
                $field->setValue( new Value( $this->defaultValues ) );
            }
            return;
        }

        $value = $this->joinUserData(
            $this->fieldData[$content->id],
            $userData[$content->id]
        );

        $field->setValue( $value );
    }

    /**
     * Join user data into field data
     *
     * @param array $data
     * @param array $userData
     *
     * @return array
     */
    protected function joinUserData( $data, $userData )
    {
        $data->contentId        = $userData->id;
        $data->hasStoredLogin   = true;
        $data->login            = $userData->login;
        $data->email            = $userData->email;
        $data->passwordHash     = $userData->passwordHash;
        $data->passwordHashType = $userData->hashAlgorithm;
        $data->enabled          = $userData->enabled;

        return $data;
    }
}

