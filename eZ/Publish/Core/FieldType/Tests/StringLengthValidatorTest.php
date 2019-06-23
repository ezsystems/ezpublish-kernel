<?php

/**
 * File containing the StringLengthValidatorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\FieldType\Validator\StringLengthValidator;
use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\API\Repository\Values\Translation\Plural;
use eZ\Publish\SPI\FieldType\ValidationError;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group validator
 */
class StringLengthValidatorTest extends TestCase
{
    /**
     * @return int
     */
    protected function getMinStringLength()
    {
        return 5;
    }

    /**
     * @return int
     */
    protected function getMaxStringLength()
    {
        return 10;
    }

    /**
     * This test ensure an StringLengthValidator can be created.
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            Validator::class,
            new StringLengthValidator()
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
            'minStringLength' => 5,
            'maxStringLength' => 10,
        ];
        $validator = new StringLengthValidator();
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame($constraints['minStringLength'], $validator->minStringLength);
        $this->assertSame($constraints['maxStringLength'], $validator->maxStringLength);
    }

    /**
     * Test getting constraints schema.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = [
            'minStringLength' => [
                'type' => 'int',
                'default' => 0,
            ],
            'maxStringLength' => [
                'type' => 'int',
                'default' => null,
            ],
        ];
        $validator = new StringLengthValidator();
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
            'minStringLength' => 5,
            'maxStringLength' => 10,
        ];
        $validator = new StringLengthValidator();
        $validator->minStringLength = $constraints['minStringLength'];
        $validator->maxStringLength = $constraints['maxStringLength'];
        $this->assertSame($constraints['minStringLength'], $validator->minStringLength);
        $this->assertSame($constraints['maxStringLength'], $validator->maxStringLength);
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
        $validator = new StringLengthValidator();
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
        $validator = new StringLengthValidator();
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
        $validator = new StringLengthValidator();
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value.
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\StringLengthValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues($value)
    {
        $validator = new StringLengthValidator();
        $validator->minStringLength = 5;
        $validator->maxStringLength = 10;
        $this->assertTrue($validator->validate(new TextLineValue($value)));
        $this->assertSame([], $validator->getMessage());
    }

    public function providerForValidateOK()
    {
        return [
            ['hello'],
            ['hello!'],
            ['0123456789'],
            ['♔♕♖♗♘♙♚♛♜♝'],
        ];
    }

    /**
     * Tests validating a wrong value.
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\StringLengthValidator::validate
     */
    public function testValidateWrongValues($value, $messageSingular, $messagePlural, $values)
    {
        $validator = new StringLengthValidator();
        $validator->minStringLength = $this->getMinStringLength();
        $validator->maxStringLength = $this->getMaxStringLength();
        $this->assertFalse($validator->validate(new TextLineValue($value)));
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
            $messageSingular,
            $messages[0]->getTranslatableMessage()->singular
        );
        $this->assertEquals(
            $messagePlural,
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
                '',
                'The string cannot be shorter than %size% character.',
                'The string cannot be shorter than %size% characters.',
                ['%size%' => $this->getMinStringLength()],
            ],
            [
                'Hi!',
                'The string cannot be shorter than %size% character.',
                'The string cannot be shorter than %size% characters.',
                ['%size%' => $this->getMinStringLength()],
            ],
            [
                '0123456789!',
                'The string can not exceed %size% character.',
                'The string can not exceed %size% characters.',
                ['%size%' => $this->getMaxStringLength()],
            ],
            [
                'ABC♔',
                'The string cannot be shorter than %size% character.',
                'The string cannot be shorter than %size% characters.',
                ['%size%' => $this->getMinStringLength()],
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
        $validator = new StringLengthValidator();

        $this->assertEmpty(
            $validator->validateConstraints($constraints)
        );
    }

    public function providerForValidateConstraintsOK()
    {
        return [
            [
                [],
                [
                    'minStringLength' => 5,
                ],
                [
                    'maxStringLength' => 2,
                ],
                [
                    'minStringLength' => false,
                    'maxStringLength' => false,
                ],
                [
                    'minStringLength' => -5,
                    'maxStringLength' => false,
                ],
                [
                    'minStringLength' => false,
                    'maxStringLength' => 12,
                ],
                [
                    'minStringLength' => 6,
                    'maxStringLength' => 8,
                ],
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
        $validator = new StringLengthValidator();
        $messages = $validator->validateConstraints($constraints);

        foreach ($expectedMessages as $index => $expectedMessage) {
            $this->assertInstanceOf(
                Message::class,
                $messages[0]->getTranslatableMessage()
            );
            $this->assertEquals(
                $expectedMessage,
                $messages[$index]->getTranslatableMessage()->message
            );
            $this->assertEquals(
                $values[$index],
                $messages[$index]->getTranslatableMessage()->values
            );
        }
    }

    public function providerForValidateConstraintsKO()
    {
        return [
            [
                [
                    'minStringLength' => true,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minStringLength'],
                ],
            ],
            [
                [
                    'minStringLength' => 'five thousand characters',
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minStringLength'],
                ],
            ],
            [
                [
                    'minStringLength' => 'five thousand characters',
                    'maxStringLength' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minStringLength'],
                ],
            ],
            [
                [
                    'maxStringLength' => new \DateTime(),
                    'minStringLength' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'maxStringLength'],
                ],
            ],
            [
                [
                    'minStringLength' => true,
                    'maxStringLength' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minStringLength'],
                ],
            ],
            [
                [
                    'minStringLength' => 'five thousand characters',
                    'maxStringLength' => 'ten billion characters',
                ],
                [
                    "Validator parameter '%parameter%' value must be of integer type",
                    "Validator parameter '%parameter%' value must be of integer type",
                ],
                [
                    ['%parameter%' => 'minStringLength'],
                    ['%parameter%' => 'maxStringLength'],
                ],
            ],
            [
                [
                    'brljix' => 12345,
                ],
                ["Validator parameter '%parameter%' is unknown"],
                [
                    ['%parameter%' => 'brljix'],
                ],
            ],
            [
                [
                    'minStringLength' => 12345,
                    'brljix' => 12345,
                ],
                ["Validator parameter '%parameter%' is unknown"],
                [
                    ['%parameter%' => 'brljix'],
                ],
            ],
        ];
    }
}
