<?php

/**
 * File containing the FileSizeValidatorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\Core\FieldType\Validator\FileSizeValidator;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\API\Repository\IOServiceInterface;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\SPI\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\Translation\Plural;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group validator
 */
class FileSizeValidatorTest extends TestCase
{
    /**
     * @return int
     */
    protected function getMaxFileSize()
    {
        return 4096;
    }

    /**
     * This test ensure an FileSizeValidator can be created.
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            Validator::class,
            new FileSizeValidator()
        );
    }

    /**
     * Tests setting and getting constraints.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::initializeWithConstraints
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsInitializeGet()
    {
        $constraints = [
            'maxFileSize' => 4096,
        ];
        $validator = new FileSizeValidator();
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame($constraints['maxFileSize'], $validator->maxFileSize);
    }

    /**
     * Test getting constraints schema.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = [
            'maxFileSize' => [
                'type' => 'int',
                'default' => false,
            ],
        ];
        $validator = new FileSizeValidator();
        $this->assertSame($constraintsSchema, $validator->getConstraintsSchema());
    }

    /**
     * Tests setting and getting constraints.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__set
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsSetGet()
    {
        $constraints = [
            'maxFileSize' => 4096,
        ];
        $validator = new FileSizeValidator();
        $validator->maxFileSize = $constraints['maxFileSize'];
        $this->assertSame($constraints['maxFileSize'], $validator->maxFileSize);
    }

    /**
     * Tests initializing with a wrong constraint.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::initializeWithConstraints
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testInitializeBadConstraint()
    {
        $constraints = [
            'unexisting' => 0,
        ];
        $validator = new FileSizeValidator();
        $validator->initializeWithConstraints(
            $constraints
        );
    }

    /**
     * Tests setting a wrong constraint.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__set
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testSetBadConstraint()
    {
        $validator = new FileSizeValidator();
        $validator->unexisting = 0;
    }

    /**
     * Tests getting a wrong constraint.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     * @expectedException \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     */
    public function testGetBadConstraint()
    {
        $validator = new FileSizeValidator();
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value.
     *
     * @param int $size
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues($size)
    {
        $this->markTestSkipped('BinaryFile field type does not use this validator anymore.');
        $validator = new FileSizeValidator();
        $validator->maxFileSize = 4096;
        $this->assertTrue($validator->validate($this->getBinaryFileValue($size)));
        $this->assertSame([], $validator->getMessage());
    }

    /**
     * @param int $size
     *
     * @return \eZ\Publish\Core\FieldType\BinaryFile\Value
     */
    protected function getBinaryFileValue($size)
    {
        $this->markTestSkipped('BinaryFile field type does not use this validator anymore.');
        $value = new BinaryFileValue($this->createMock(IOServiceInterface::class));
        $value->file = new BinaryFile(['size' => $size]);

        return $value;
    }

    public function providerForValidateOK()
    {
        return [
            [0],
            [512],
            [4096],
        ];
    }

    /**
     * Tests validating a wrong value.
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validate
     */
    public function testValidateWrongValues($size, $message, $values)
    {
        $this->markTestSkipped('BinaryFile field type does not use this validator anymore.');
        $validator = new FileSizeValidator();
        $validator->maxFileSize = $this->getMaxFileSize();
        $this->assertFalse($validator->validate($this->getBinaryFileValue($size)));
        $messages = $validator->getMessage();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(
            ValidationError::class,
            $messages[0]
        );
        $this->assertInstanceOf(
            Plural::class,
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $message[0],
            $messages[0]->getTranslatableMessage()->singular
        );
        $this->assertEquals(
            $message[1],
            $messages[0]->getTranslatableMessage()->plural
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateKO()
    {
        return [
            [
                8192,
                [
                    'The file size cannot exceed %size% byte.',
                    'The file size cannot exceed %size% bytes.',
                ],
                ['%size%' => $this->getMaxFileSize()],
            ],
        ];
    }

    /**
     * Tests validation of constraints.
     *
     * @dataProvider providerForValidateConstraintsOK
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validateConstraints
     */
    public function testValidateConstraintsCorrectValues($constraints)
    {
        $validator = new FileSizeValidator();

        $this->assertEmpty(
            $validator->validateConstraints($constraints)
        );
    }

    public function providerForValidateConstraintsOK()
    {
        return [
            [
                [],
                ['maxFileSize' => false],
                ['maxFileSize' => 0],
                ['maxFileSize' => -5],
                ['maxFileSize' => 4096],
            ],
        ];
    }

    /**
     * Tests validation of constraints.
     *
     * @dataProvider providerForValidateConstraintsKO
     * @covers \eZ\Publish\Core\FieldType\Validator\FileSizeValidator::validateConstraints
     */
    public function testValidateConstraintsWrongValues($constraints, $expectedMessages, $values)
    {
        $validator = new FileSizeValidator();
        $messages = $validator->validateConstraints($constraints);

        $this->assertInstanceOf(
            Message::class,
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $expectedMessages[0],
            $messages[0]->getTranslatableMessage()->message
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateConstraintsKO()
    {
        return [
            [
                ['maxFileSize' => true],
                ["Validator parameter '%parameter%' value must be of integer type"],
                ['%parameter%' => 'maxFileSize'],
            ],
            [
                ['maxFileSize' => 'five thousand bytes'],
                ["Validator parameter '%parameter%' value must be of integer type"],
                ['%parameter%' => 'maxFileSize'],
            ],
            [
                ['maxFileSize' => new \DateTime()],
                ["Validator parameter '%parameter%' value must be of integer type"],
                ['%parameter%' => 'maxFileSize'],
            ],
            [
                ['brljix' => 12345],
                ["Validator parameter '%parameter%' is unknown"],
                ['%parameter%' => 'brljix'],
            ],
        ];
    }
}
