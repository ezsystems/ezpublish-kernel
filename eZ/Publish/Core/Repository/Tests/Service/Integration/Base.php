<?php
/**
 * File contains: Abstract Base service test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use PHPUnit_Framework_TestCase;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Base test case for tests on services
 * Initializes repository
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * Setup test
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repository = static::getRepository();
        $this->repository->setCurrentUser( $this->getStubbedUser( 14 ) );
    }

    /**
     * Returns User stub with $id as User/Content id
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser( $id )
    {
        return new User(
            array(
                'content' => new Content(
                    array(
                        'versionInfo' => new VersionInfo(
                            array(
                                'contentInfo' => new ContentInfo( array( 'id' => $id ) )
                            )
                        ),
                        'internalFields' => array()
                    )
                )
            )
        );
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\User\User
     */
    protected function createUserVersion1()
    {
        $repository = $this->repository;

        /* BEGIN: Inline */
        // ID of the "Editors" user group in an eZ Publish demo installation
        $editorsGroupId = 13;

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField( 'first_name', 'Example' );
        $userCreate->setField( 'last_name', 'User' );

        // Load parent group for the user
        $group = $userService->loadUserGroup( $editorsGroupId );

        // Create a new user instance.
        $user = $userService->createUser( $userCreate, array( $group ) );
        /* END: Inline */

        return $user;
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->repository );
        parent::tearDown();
    }

    /**
     * Generate \eZ\Publish\API\Repository\Repository
     *
     * Makes it possible to inject different Io / Persistence handlers
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    abstract protected function getRepository();

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $skipProperties
     *
     * @return void
     */
    protected function assertPropertiesCorrect( array $expectedValues, ValueObject $actualObject, array $skipProperties = array() )
    {
        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            if ( in_array( $propertyName, $skipProperties ) ) continue;

            $this->assertProperty(
                $propertyName, $propertyValue, $actualObject->$propertyName
            );
        }
    }

    protected function assertSameClassPropertiesCorrect(
        array $propertiesNames,
        ValueObject $expectedValues,
        ValueObject $actualObject,
        array $skipProperties = array(),
        $equal = true
    )
    {
        foreach ( $propertiesNames as $propertyName )
        {
            if ( in_array( $propertyName, $skipProperties ) ) continue;

            $this->assertProperty(
                $propertyName, $expectedValues->$propertyName, $actualObject->$propertyName, $equal
            );
        }
    }

    /**
     * Asserts all properties from $expectedValues are correctly set in
     * $actualObject.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $skipProperties
     *
     * @return void
     */
    protected function assertStructPropertiesCorrect( ValueObject $expectedValues, ValueObject $actualObject, array $skipProperties = array() )
    {
        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            if ( in_array( $propertyName, $skipProperties ) ) continue;

            $this->assertProperty(
                $propertyName, $propertyValue, $actualObject->$propertyName
            );
        }
    }

    private function assertProperty( $propertyName, $expectedValue, $actualValue, $equal = true )
    {
        if ( $expectedValue instanceof \ArrayObject )
        {
            $expectedValue = $expectedValue->getArrayCopy();
        }
        if ( $actualValue instanceof \ArrayObject )
        {
            $actualValue = $actualValue->getArrayCopy();
        }

        if ( $equal )
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                sprintf( 'Object property "%s" incorrect.', $propertyName )
            );
        else
            $this->assertNotEquals(
                $expectedValue,
                $actualValue,
                sprintf( 'Object property "%s" incorrect.', $propertyName )
            );
    }

    protected function getDateTime( $timestamp )
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp( $timestamp );
        return $dateTime;
    }
}
