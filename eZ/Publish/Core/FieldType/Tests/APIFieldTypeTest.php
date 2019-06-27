<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\Repository\Values\ContentType\FieldType;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use PHPUnit\Framework\TestCase;

class APIFieldTypeTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $innerFieldType;

    /** @var FieldType */
    private $fieldType;

    protected function setUp()
    {
        parent::setUp();
        $this->innerFieldType = $this->createMock(SPIFieldType::class);
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
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
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
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
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
        $fieldDefinition = $this->getMockForAbstractClass(APIFieldDefinition::class);
        $value = $this->getMockForAbstractClass(Value::class);
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
        $fieldDefinition = $this->getMockForAbstractClass(APIFieldDefinition::class);
        $value = $this->getMockForAbstractClass(Value::class);
        $validationErrors = [
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
            $this->createMock(ValidationError::class),
        ];
        $this->innerFieldType
            ->expects($this->once())
            ->method('validate')
            ->with($fieldDefinition, $value)
            ->willReturn($validationErrors);

        self::assertSame($validationErrors, $this->fieldType->validateValue($fieldDefinition, $value));
    }
}
