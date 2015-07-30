<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\Repository\Values\ContentType\FieldType;
use PHPUnit_Framework_TestCase;

class APIFieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerFieldType;

    /**
     * @var FieldType
     */
    private $fieldType;

    protected function setUp()
    {
        parent::setUp();
        $this->innerFieldType = $this->getMock('\eZ\Publish\SPI\FieldType\FieldType');
        $this->fieldType = new FieldType($this->innerFieldType);
    }

    public function testValidateValidatorConfigurationNoError()
    {
        $validatorConfig = ['foo' => 'bar'];
        $validationErrors = [];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validateValidatorConfiguration')
            ->with($validatorConfig)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateValidatorConfiguration($validatorConfig));
    }

    public function testValidateValidatorConfiguration()
    {
        $validatorConfig = ['foo' => 'bar'];
        $validationErrors = [
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
        ];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validateValidatorConfiguration')
            ->with($validatorConfig)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateValidatorConfiguration($validatorConfig));
    }

    public function testValidateFieldSettingsNoError()
    {
        $fieldSettings = ['foo' => 'bar'];
        $validationErrors = [];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validateFieldSettings')
            ->with($fieldSettings)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateFieldSettings($fieldSettings));
    }

    public function testValidateFieldSettings()
    {
        $fieldSettings = ['foo' => 'bar'];
        $validationErrors = [
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
        ];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validateFieldSettings')
            ->with($fieldSettings)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateFieldSettings($fieldSettings));
    }

    public function testValidateValueNoError()
    {
        $fieldDefinition = $this->getMockForAbstractClass('\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition');
        $value = $this->getMockForAbstractClass('\eZ\Publish\Core\FieldType\Value');
        $validationErrors = [];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validate')
            ->with($fieldDefinition, $value)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateValue($fieldDefinition, $value));
    }

    public function testValidateValue()
    {
        $fieldDefinition = $this->getMockForAbstractClass('\eZ\Publish\API\Repository\Values\ContentType\FieldDefinition');
        $value = $this->getMockForAbstractClass('\eZ\Publish\Core\FieldType\Value');
        $validationErrors = [
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
            $this->getMock('\eZ\Publish\SPI\FieldType\ValidationError'),
        ];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validate')
            ->with($fieldDefinition, $value)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateValue($fieldDefinition, $value));
    }
}
