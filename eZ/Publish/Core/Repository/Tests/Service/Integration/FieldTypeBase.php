<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\FieldTypeBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;

/**
 * Test case for FieldType service
 */
abstract class FieldTypeBase extends BaseServiceTest
{
    protected $fieldIdentifiersData;

    /**
     * Test for the getFieldTypes() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::getFieldTypes
     */
    public function testGetFieldTypes()
    {
        $fieldTypeService = $this->repository->getFieldTypeService();
        $refObject = new \ReflectionObject( $fieldTypeService );
        $refProperty = $refObject->getProperty( 'settings' );
        $refProperty->setAccessible( true );
        $fieldTypeSettings = $refProperty->getValue( $fieldTypeService );

        $fieldTypes = $fieldTypeService->getFieldTypes();

        self::assertEquals( count( $fieldTypeSettings ), count( $fieldTypes ) );
        foreach ( $fieldTypes as $fieldType )
        {
            self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\FieldType", $fieldType );
            self::assertInternalType( "string", $fieldType->getFieldTypeIdentifier() );
            self::assertArrayHasKey( $fieldType->getFieldTypeIdentifier(), $fieldTypeSettings );
        }
    }

    protected function getFieldIdentifiersData()
    {
        if ( !isset( $this->fieldIdentifiersData ) )
        {
            $fieldTypeService = $this->getRepository()->getFieldTypeService();
            $refObject = new \ReflectionObject( $fieldTypeService );
            $refProperty = $refObject->getProperty( 'settings' );
            $refProperty->setAccessible( true );
            $fieldTypeIdentifiers = array_keys( $refProperty->getValue( $fieldTypeService ) );

            $this->fieldIdentifiersData = array_map(
                function ( $identifier )
                {
                    return array( $identifier );
                },
                $fieldTypeIdentifiers
            );
        }

        return $this->fieldIdentifiersData;
    }

    public function providerForTestGetFieldType()
    {
        return $this->getFieldIdentifiersData();
    }

    /**
     * Test for the getFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::getFieldType
     * @dataProvider providerForTestGetFieldType
     */
    public function testGetFieldType( $identifier )
    {
        $fieldTypeService = $this->repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType( $identifier );

        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\FieldType", $fieldType );
        self::assertInternalType( "string", $fieldType->getFieldTypeIdentifier() );
        self::assertEquals( $identifier, $fieldType->getFieldTypeIdentifier() );
    }

    public function providerForTestGetFieldTypeThrowsNotFoundException()
    {
        return array(
            array( "EZSTRING" ),
            array( "thingamajigger" )
        );
    }

    /**
     * Test for the getFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::getFieldType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestGetFieldTypeThrowsNotFoundException
     */
    public function testGetFieldTypeThrowsNotFoundException( $identifier )
    {
        $fieldTypeService = $this->repository->getFieldTypeService();
        $fieldTypeService->getFieldType( $identifier );
    }

    public function providerForTestHasFieldTypeTrue()
    {
        return $this->getFieldIdentifiersData();
    }

    /**
     * Test for the hasFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::hasFieldType
     * @dataProvider providerForTestHasFieldTypeTrue
     */
    public function testHasFieldTypeTrue( $identifier )
    {
        self::assertTrue(
            $this->repository->getFieldTypeService()->hasFieldType( $identifier )
        );
    }

    public function providerForTestHasFieldTypeFalse()
    {
        return array(
            array( "EZSTRING" ),
            array( "thingamajigger" )
        );
    }

    /**
     * Test for the hasFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::hasFieldType
     * @dataProvider providerForTestHasFieldTypeFalse
     */
    public function testHasFieldTypeFalse( $identifier )
    {
        self::assertFalse(
            $this->repository->getFieldTypeService()->hasFieldType( $identifier )
        );
    }

    public function providerForTestBuildFieldType()
    {
        return $this->getFieldIdentifiersData();
    }

    /**
     * Test for the buildFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::buildFieldType
     * @dataProvider providerForTestBuildFieldType
     */
    public function testBuildFieldType( $identifier )
    {
        $fieldTypeService = $this->repository->getFieldTypeService();
        $fieldType = $fieldTypeService->buildFieldType( $identifier );

        self::assertInstanceOf( "eZ\\Publish\\Core\\FieldType\\FieldType", $fieldType );
        $fieldTypeIdentifier = $fieldType->getFieldTypeIdentifier();
        self::assertInternalType( "string", $fieldType->getFieldTypeIdentifier() );
        self::assertEquals( $identifier, $fieldType->getFieldTypeIdentifier() );
    }

    public function providerForTestBuildFieldTypeThrowsNotFoundException()
    {
        return array(
            array( "EZSTRING" ),
            array( "thingamajigger" )
        );
    }

    /**
     * Test for the buildFieldType() method.
     *
     * @covers \eZ\Publish\Core\Repository\FieldTypeService::buildFieldType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestBuildFieldTypeThrowsNotFoundException
     */
    public function testBuildFieldTypeThrowsNotFoundException( $identifier )
    {
        $fieldTypeService = $this->repository->getFieldTypeService();
        $fieldTypeService->getFieldType( $identifier );
    }
}
