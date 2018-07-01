<?php

/**
 * File containing the FieldTypeServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Client\FieldTypeService;
use eZ\Publish\Core\REST\Client\FieldType;
use PHPUnit\Framework\TestCase;

class FieldTypeServiceTest extends TestCase
{
    public function testHasFieldType()
    {
        $fieldTypeService = $this->getFieldTypeService();

        $this->assertFalse(
            $fieldTypeService->hasFieldType('my-type')
        );
    }

    public function testAddFieldType()
    {
        $fieldTypeMock = $this->createMock(FieldType::class);
        $fieldTypeMock->expects($this->once())
            ->method('getFieldTypeIdentifier')
            ->will($this->returnValue('my-type'));

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType($fieldTypeMock);

        $this->assertTrue(
            $fieldTypeService->hasFieldType('my-type')
        );
    }

    public function testGetFieldType()
    {
        $fieldTypeMock = $this->createMock(FieldType::class);
        $fieldTypeMock->expects($this->once())
            ->method('getFieldTypeIdentifier')
            ->will($this->returnValue('my-type'));

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType($fieldTypeMock);

        $this->assertSame(
            $fieldTypeMock,
            $fieldTypeService->getFieldType('my-type')
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testGetFieldTypeThrowsNotFoundException()
    {
        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->getFieldType('my-type');
    }

    public function testGetFieldTypes()
    {
        $myFieldTypeMock = $this->createMock(FieldType::class);
        $myFieldTypeMock->expects($this->once())
            ->method('getFieldTypeIdentifier')
            ->will($this->returnValue('my-type'));

        $yourFieldTypeMock = $this->createMock(FieldType::class);
        $yourFieldTypeMock->expects($this->once())
            ->method('getFieldTypeIdentifier')
            ->will($this->returnValue('your-type'));

        $fieldTypeService = $this->getFieldTypeService();

        $fieldTypeService->addFieldType($myFieldTypeMock);
        $fieldTypeService->addFieldType($yourFieldTypeMock);

        $this->assertEquals(
            2,
            count($fieldTypeService->getFieldTypes())
        );
    }

    public function getFieldTypeService()
    {
        return new FieldTypeService();
    }
}
