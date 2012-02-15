<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \PHPUnit_Framework_TestCase;
use \eZ\Publish\API\Repository\Repository;
use \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub;

use \eZ\Publish\API\Repository\Values\ValueObject;


/**
 * Base class for api specific tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

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
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        if ( null === $this->repository )
        {
            // TODO: REMOVE THIS WORKAROUND AND CREATE A FRESH USER
            $user   = new UserStub( array( 'id' => 1 ) );
            $policy = new PolicyStub( array( 'module' => '*', 'function' => '*' ) );

            $this->repository = new RepositoryStub();
            $this->repository->setCurrentUser( $user );
            // TODO: REMOVE THIS WORKAROUND AND CREATE POLICIES
            $this->repository->getRoleService()->setPoliciesForUser( $user, array( $policy ) );
        }
        return $this->repository;
    }

    /**
     * Workaround to emulate user policies.
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepositoryWithRestriction( $module, $function )
    {
        if ( null === $this->repository )
        {
            $this->repository = $this->getRepository();
        }

        // TODO: REMOVE THIS WORKAROUND AND CREATE POLICIES
        $this->repository->getRoleService()->setPoliciesForUser(
            $this->repository->getCurrentUser(),
            array( new PolicyStub( array( 'module' => $module, 'function' => $function ) ) )
        );

        return $this->repository;
    }

    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $actualObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
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
     * Asserts all properties from $expectedValues are correctly set in
     * $actualObject. Additional (virtual) properties can be asserted using
     * $additionalProperties.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualObject
     * @param array $propertyNames
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

    private function assertPropertiesEqual( $propertyName, $expectedValue, $actualValue )
    {
        if( $expectedValue instanceof \ArrayObject )
        {
            $expectedValue = $expectedValue->getArrayCopy();
        }
        if( $actualValue instanceof \ArrayObject )
        {
            $actualValue = $actualValue->getArrayCopy();
        }

        $this->assertEquals(
            $expectedValue,
            $actualValue,
            sprintf( 'Object property "%s" incorrect.', $propertyName )
        );
    }
}
