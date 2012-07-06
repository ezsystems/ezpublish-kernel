<?php
/**
 * File contains: Abstract Base service test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use PHPUnit_Framework_TestCase,
    eZ\Publish\API\Repository\Values\ValueObject,
    eZ\Publish\Core\Repository\Values\User\User,
    eZ\Publish\Core\Repository\Values\Content\VersionInfo,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo;

/**
 * Base test case for tests on services
 * Initializes repository
 */
abstract class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * Setup test
     */
    protected function setUp()
    {
        parent::setUp();
        $serviceSettings = array(
            'contentType' => array(
                'field_type' => array(
                    'ezauthor' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Author\Type(); },
                    'ezdatetime' => function(){ return new \eZ\Publish\Core\Repository\FieldType\DateAndTime\Type(); },
                    'ezfloat' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Float\Type(); },
                    'ezinteger' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Integer\Type(); },
                    'ezkeyword' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Keyword\Type(); },
                    'eztext' => function(){ return new \eZ\Publish\Core\Repository\FieldType\TextBlock\Type(); },
                    'ezstring' => function(){ return new \eZ\Publish\Core\Repository\FieldType\TextLine\Type(); },
                    'ezimage' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Integer\Type(); },
                    'ezuser' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Integer\Type(); },
                    'ezurl' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Url\Type(); },
                    'ezxmltext' => function(){ return new \eZ\Publish\Core\Repository\FieldType\XmlText\Type(
                        new \eZ\Publish\Core\Repository\FieldType\XmlText\Input\Parser\Simplified(
                            new \eZ\Publish\Core\Repository\FieldType\XmlText\Schema
                        )
                    ); },
                ),
            ),
        );
        $this->repository = static::getRepository( $serviceSettings );
    }

    /**
     * Returns User stub with $id as User/Content id
     *
     * @param $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser( $id )
    {
        return new User(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo( array( 'id' => $id ) )
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
     * Generate \eZ\Publish\Core\Repository\Repository
     *
     * Makes it possible to inject different Io / Persistence handlers
     *
     * @param array $serviceSettings Array with settings that are passed to Services
     * @return \eZ\Publish\Core\Repository\Repository
     */
    abstract protected function getRepository( array $serviceSettings );

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
        if( $expectedValue instanceof \ArrayObject )
        {
            $expectedValue = $expectedValue->getArrayCopy();
        }
        if( $actualValue instanceof \ArrayObject )
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
