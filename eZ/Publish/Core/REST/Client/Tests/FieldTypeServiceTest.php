<?php
/**
 * File containing the FieldTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Client\FieldTypeService;

class FieldTypeServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testHasFieldType()
    {
        $fieldTypeService = $this->getFieldTypeService();

        $this->assertFalse(
            $fieldTypeService->hasFieldType( 'my-type' )
        );
    }

    public function testAddFieldType()
    {
        $fieldTypeMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\FieldType',
            array(),
            array(),
            '',
            false
        );
        $fieldTypeMock->expects( $this->once() )
            ->method( 'getFieldTypeIdentifier' )
            ->will( $this->returnValue( 'my-type' ) );

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType( $fieldTypeMock );

        $this->assertTrue(
            $fieldTypeService->hasFieldType( 'my-type' )
        );
    }

    public function testGetFieldType()
    {
        $fieldTypeMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\FieldType',
            array(),
            array(),
            '',
            false
        );
        $fieldTypeMock->expects( $this->once() )
            ->method( 'getFieldTypeIdentifier' )
            ->will( $this->returnValue( 'my-type' ) );

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType( $fieldTypeMock );

        $this->assertSame(
            $fieldTypeMock,
            $fieldTypeService->getFieldType( 'my-type' )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testGetFieldTypeThrowsNotFoundException()
    {
        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->getFieldType( 'my-type' );
    }

    public function testGetFieldTypes()
    {
        $myFieldTypeMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\FieldType',
            array(),
            array(),
            '',
            false
        );
        $myFieldTypeMock->expects( $this->once() )
            ->method( 'getFieldTypeIdentifier' )
            ->will( $this->returnValue( 'my-type' ) );

        $yourFieldTypeMock = $this->getMock(
            'eZ\\Publish\\Core\\REST\\Client\\FieldType',
            array(),
            array(),
            '',
            false
        );
        $yourFieldTypeMock->expects( $this->once() )
            ->method( 'getFieldTypeIdentifier' )
            ->will( $this->returnValue( 'your-type' ) );

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType( $myFieldTypeMock );
        $fieldTypeService->addFieldType( $yourFieldTypeMock );

        $this->assertEquals(
            2,
            count( $fieldTypeService->getFieldTypes() )
        );
    }

    public function getFieldTypeService()
    {
        return new FieldTypeService();
    }
}
