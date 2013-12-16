<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use PHPUnit_Framework_TestCase;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\Core\REST\Client\Sessionable;
use DateTime;
use ArrayObject;
use Exception;
use PDOException;

/**
 * Base class for api specific tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Maximum integer number accepted by the different backends
     */
    const DB_INT_MAX = 2147483647;

    /**
     * @var \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    private $setupFactory;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        try
        {
            // Use setup factory instance here w/o clearing data in case test don't need to
            $repository = $this->getSetupFactory()->getRepository( false );

            // Set session if we are testing the REST backend to make it
            // possible to persist data in the memory backend during multiple
            // requests.
            if ( $repository instanceof Sessionable )
            {
                $repository->setSession( $id = md5( microtime() ) );
            }
        }
        catch ( PDOException $e )
        {
            $this->fail(
                "The communication with the database cannot be established. " .
                "This is required in order to perform the tests.\n\n" .
                "Exception: " . $e
            );
        }
        catch ( Exception $e )
        {
            $this->markTestSkipped(
                'Cannot create a repository with predefined user. ' .
                'Check the UserService or RoleService implementation. ' .
                PHP_EOL . PHP_EOL .
                'Exception: ' . $e
            );
        }
    }

    /**
     * Resets the temporary used repository between each test run.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->repository = null;
        parent::tearDown();
    }

    /**
     * Returns the ID generator, fitting to the repository implementation
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    protected function getIdManager()
    {
        return $this->getSetupFactory()->getIdManager();
    }

    /**
     * Generates a repository specific ID value.
     *
     * @param string $type
     * @param mixed $rawId
     *
     * @return mixed
     */
    protected function generateId( $type, $rawId )
    {
        return $this->getIdManager()->generateId( $type, $rawId );
    }

    /**
     * Parses a repository specific ID value.
     *
     * @param string $type
     * @param mixed $id
     *
     * @return mixed
     */
    protected function parseId( $type, $id )
    {
        return $this->getIdManager()->parseId( $type, $id );
    }

    /**
     * Returns a config setting provided by the setup factory
     *
     * @param string $configKey
     *
     * @return mixed
     */
    protected function getConfigValue( $configKey )
    {
        return $this->getSetupFactory()->getConfigValue( $configKey );
    }

    /**
     * Tests if the currently tested api is based on a V4 implementation.
     *
     * @return boolean
     */
    protected function isVersion4()
    {
        return ( isset( $_ENV['backendVersion'] ) && '4' === $_ENV['backendVersion'] );
    }

    /**
     * @param bool $initialInitializeFromScratch Only has an effect if set in first call within a test
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository( $initialInitializeFromScratch = true  )
    {
        if ( null === $this->repository )
        {
            $this->repository = $this->getSetupFactory()->getRepository( $initialInitializeFromScratch );
        }
        return $this->repository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Tests\SetupFactory
     */
    protected function getSetupFactory()
    {
        if ( null === $this->setupFactory )
        {
            if ( false === isset( $_ENV['setupFactory'] ) )
            {
                throw new \ErrorException( 'Missing mandatory setting $_ENV["setupFactory"].' );
            }

            $setupClass = $_ENV['setupFactory'];
            if ( false === class_exists( $setupClass ) )
            {
                throw new \ErrorException( '$_ENV["setupFactory"] does not reference an existing class.' );
            }

            $this->setupFactory = new $setupClass;
        }
        return $this->setupFactory;
    }

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     *
     * @return void
     */
    protected function assertPropertiesCorrect( array $expectedValues, ValueObject $actualObject )
    {
        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            $this->assertPropertiesEqual(
                $propertyName, $propertyValue, $actualObject->$propertyName
            );
        }
    }

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * If the property type is array, it will be sorted before comparison.
     * @TODO: introduced because of randomly failing tests, ref: https://jira.ez.no/browse/EZP-21734
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     *
     * @return void
     */
    protected function assertPropertiesCorrectUnsorted( array $expectedValues, ValueObject $actualObject )
    {
        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            $this->assertPropertiesEqual(
                $propertyName, $propertyValue, $actualObject->$propertyName, true
            );
        }
    }

    /**
     * Asserts all properties from $expectedValues are correctly set in
     * $actualObject. Additional (virtual) properties can be asserted using
     * $additionalProperties.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $propertyNames
     *
     * @return void
     */
    protected function assertStructPropertiesCorrect( ValueObject $expectedValues, ValueObject $actualObject, array $additionalProperties = array() )
    {
        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            $this->assertPropertiesEqual(
                $propertyName, $propertyValue, $actualObject->$propertyName
            );
        }

        foreach ( $additionalProperties as $propertyName )
        {
            $this->assertPropertiesEqual(
                $propertyName, $expectedValues->$propertyName, $actualObject->$propertyName
            );
        }
    }

    /**
     * @see \eZ\Publish\API\Repository\Tests\BaseTest::assertPropertiesCorrectUnsorted()
     *
     * @param array $items An array of scalar values
     */
    private function sortItems( array &$items )
    {
        $sorter = function ( $a, $b )
        {
            if ( !is_scalar( $a ) || !is_scalar( $b ) )
            {
                $this->fail( "Wrong usage: method " . __METHOD__ . " accepts only an array of scalar values" );
            }
            return strcmp( $a, $b );
        };
        usort( $items, $sorter );
    }

    private function assertPropertiesEqual( $propertyName, $expectedValue, $actualValue, $sortArray = false )
    {
        if ( $expectedValue instanceof ArrayObject )
        {
            $expectedValue = $expectedValue->getArrayCopy();
        }
        else if ( $expectedValue instanceof DateTime )
        {
            $expectedValue = $expectedValue->format( DateTime::RFC850 );
        }
        if ( $actualValue instanceof ArrayObject )
        {
            $actualValue = $actualValue->getArrayCopy();
        }
        else if ( $actualValue instanceof DateTime )
        {
            $actualValue = $actualValue->format( DateTime::RFC850 );
        }

        if ( $sortArray && is_array( $actualValue ) && is_array( $expectedValue ) )
        {
            $this->sortItems( $actualValue );
            $this->sortItems( $expectedValue );
        }

        $this->assertEquals(
            $expectedValue,
            $actualValue,
            sprintf( 'Object property "%s" incorrect.', $propertyName )
        );
    }

    /**
     * Create a user fixture in a variable named <b>$user</b>,
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createUserVersion1()
    {
        $repository = $this->getRepository();

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
     * Create a user fixture in a variable named <b>$user</b>,
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createMediaUserVersion1()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // ID of the "Users" user group in an eZ Publish demo installation
        $usersGroupId = 4;

        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        // Get a group create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Media Editor' );

        // Create new group with media editor rights
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup( $usersGroupId )
        );
        $roleService->assignRoleToUserGroup(
            $roleService->loadRoleByIdentifier( 'Editor' ),
            $userGroup,
            new SubtreeLimitation(
                array(
                    'limitationValues' => array( '/1/48/' )
                )
            )
        );

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

        // Create a new user instance.
        $user = $userService->createUser( $userCreate, array( $userGroup ) );
        /* END: Inline */

        return $user;
    }

    /**
     * Only for internal use.
     *
     * Creates a \DateTime object for $timestamp in the current time zone
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    public function createDateTime( $timestamp = null )
    {
        $dateTime = new \DateTime();
        if ( $timestamp !== null )
        {
            $dateTime->setTimestamp( $timestamp );
        }
        return $dateTime;
    }
}
