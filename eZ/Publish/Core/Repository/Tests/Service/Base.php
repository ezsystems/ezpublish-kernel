<?php
/**
 * File contains: Abstract Base service test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use PHPUnit_Framework_TestCase;
use eZ\Publish\API\Repository\Values\ValueObject;

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
                    'ezurl' => function(){ return new \eZ\Publish\Core\Repository\FieldType\Url\Type(); },
                ),
            ),
        );
        $this->repository = static::getRepository( $serviceSettings );
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

            $this->assertPropertiesEqual(
                $propertyName, $propertyValue, $actualObject->$propertyName
            );
        }
    }

    protected function assertSameClassPropertiesCorrect( array $propertiesNames, ValueObject $expectedValues, ValueObject $actualObject, array $skipProperties = array() )
    {
        foreach ( $propertiesNames as $propertyName )
        {
            if ( in_array( $propertyName, $skipProperties ) ) continue;

            $this->assertPropertiesEqual(
                $propertyName, $expectedValues->$propertyName, $actualObject->$propertyName
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

            $this->assertPropertiesEqual(
                $propertyName, $propertyValue, $actualObject->$propertyName
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
