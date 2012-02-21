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


use \eZ\Publish\API\Repository\Values\ValueObject;


/**
 * Base class for api specific tests.
 */
abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $repositoryInit;

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

        chdir( __DIR__ );

        parent::tearDown();
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        if ( null === $this->repository )
        {
            $file = $this->getRepositoryInit();

            // Change working directory to project root. This is required by the
            // current legacy implementation.
            $count = substr_count( __NAMESPACE__, '\\' ) + 1;

            chdir( realpath( str_repeat( '../', $count ) ) );

            $this->repository = include $file;
            return $this->repository;


        }
        return $this->repository;
    }

    private function getRepositoryInit()
    {
        if ( null === $this->repositoryInit )
        {
            if ( false === isset( $_ENV['repositoryInit'] ) )
            {
                throw new \ErrorException( 'Missing mandatory setting $_ENV["repositoryInit"].' );
            }

            $file = realpath( $_ENV['repositoryInit'] );
            if ( false === file_exists( $file ) )
            {
                throw new \ErrorException( '$_ENV["repositoryInit"] does not reference an existing file.' );
            }

            $this->repositoryInit = $file;
        }
        return $this->repositoryInit;
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
