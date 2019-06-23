<?php

/**
 * File containing the IntegerValueValidatorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Integer\Value as IntegerValue;
use eZ\Publish\Core\FieldType\Validator\IntegerValueValidator;
use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\SPI\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\Translation\Message;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group validator
 */
class IntegerValueValidatorTest extends TestCase
{
    /**
     * @return int
     */
    protected function getMinIntegerValue()
    {
        return 10;
    }

    /**
     * @return int
     */
    protected function getMaxIntegerValue()
    {
        return 15;
    }

    /**
     * This test ensure an IntegerValueValidator can be created.
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            Validator::class,
            new IntegerValueValidator()
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
            'minIntegerValue' => 0,
            'maxIntegerValue' => 100,
        ];
        $validator = new IntegerValueValidator();
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame($constraints['minIntegerValue'], $validator->minIntegerValue);
        $this->assertSame($constraints['maxIntegerValue'], $validator->maxIntegerValue);
    }

    /**
     * Test getting constraints schema.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = [
            'minIntegerValue' => [
                'type' => 'int',
                'default' => 0,
            ],
            'maxIntegerValue' => [
                'type' => 'int',
                'default' => null,
            ],
        ];
        $validator = new IntegerValueValidator();
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
            'minIntegerValue' => 0,
            'maxIntegerValue' => 100,
        ];
        $validator = new IntegerValueValidator();
        $validator->minIntegerValue = $constraints['minIntegerValue'];
        $validator->maxIntegerValue = $constraints['maxIntegerValue'];
        $this->assertSame($constraints['minIntegerValue'], $validator->minIntegerValue);
        $this->assertSame($constraints['maxIntegerValue'], $validator->maxIntegerValue);
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
        $validator = new IntegerValueValidator();
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
        $validator = new IntegerValueValidator();
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
        $validator = new IntegerValueValidator();
        $null = $validator->unexisting;
    }

    /**
     * Tests validating a correct value.
     *
     * @dataProvider providerForValidateOK
     * @covers \eZ\Publish\Core\FieldType\Validator\IntegerValueValidator::validate
     * @covers \eZ\Publish\Core\FieldType\Validator::getMessage
     */
    public function testValidateCorrectValues($value)
    {
        $validator = new IntegerValueValidator();
        $validator->minIntegerValue = 10;
        $validator->maxIntegerValue = 15;
        $this->assertTrue($validator->validate(new IntegerValue($value)));
        $this->assertSame([], $validator->getMessage());
    }

    public function providerForValidateOK()
    {
        return [
            [10],
            [11],
            [12],
            [12.5],
            [13],
            [14],
            [15],
        ];
    }

    /**
     * Tests validating a wrong value.
     *
     * @dataProvider providerForValidateKO
     * @covers \eZ\Publish\Core\FieldType\Validator\IntegerValueValidator::validate
     */
    public function testValidateWrongValues($value, $message, $values)
    {
        $validator = new IntegerValueValidator();
        $validator->minIntegerValue = $this->getMinIntegerValue();
        $validator->maxIntegerValue = $this->getMaxIntegerValue();
        $this->assertFalse($validator->validate(new IntegerValue($value)));
        $messages = $validator->getMessage();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(
            ValidationError::class,
            $messages[0]
        );
        $this->assertInstanceOf(
            Message::class,
            $messages[0]->getTranslatableMessage()
        );
        $this->assertEquals(
            $message,
            $messages[0]->getTranslatableMessage()->message
        );
        $this->assertEquals(
            $values,
            $messages[0]->getTranslatableMessage()->values
        );
    }

    public function providerForValidateKO()
    {
        return [
            [-12, 'The value can not be lower than %size%.', ['%size%' => $this->getMinIntegerValue()]],
            [0, 'The value can not be lower than %size%.', ['%size%' => $this->getMinIntegerValue()]],
            [9, 'The value can not be lower than %size%.', ['%size%' => $this->getMinIntegerValue()]],
            [16, 'The value can not be higher than %size%.', ['%size%' => $this->getMaxIntegerValue()]],
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
        $validator = new IntegerValueValidator();

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
                    'minIntegerValue' => 5,
                ],
                [
                    'maxIntegerValue' => 2,
                ],
                [
                    'minIntegerValue' => null,
                    'maxIntegerValue' => null,
                ],
                [
                    'minIntegerValue' => -5,
                    'maxIntegerValue' => null,
                ],
                [
                    'minIntegerValue' => null,
                    'maxIntegerValue' => 12,
                ],
                [
                    'minIntegerValue' => 6,
                    'maxIntegerValue' => 8,
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
        $validator = new IntegerValueValidator();
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
                    'minIntegerValue' => true,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minIntegerValue'],
                ],
            ],
            [
                [
                    'minIntegerValue' => 'five thousand bytes',
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minIntegerValue'],
                ],
            ],
            [
                [
                    'minIntegerValue' => 'five thousand bytes',
                    'maxIntegerValue' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minIntegerValue'],
                ],
            ],
            [
                [
                    'maxIntegerValue' => new \DateTime(),
                    'minIntegerValue' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'maxIntegerValue'],
                ],
            ],
            [
                [
                    'minIntegerValue' => true,
                    'maxIntegerValue' => 1234,
                ],
                ["Validator parameter '%parameter%' value must be of integer type"],
                [
                    ['%parameter%' => 'minIntegerValue'],
                ],
            ],
            [
                [
                    'minIntegerValue' => 'five thousand bytes',
                    'maxIntegerValue' => 'ten billion bytes',
                ],
                [
                    "Validator parameter '%parameter%' value must be of integer type",
                    "Validator parameter '%parameter%' value must be of integer type",
                ],
                [
                    ['%parameter%' => 'minIntegerValue'],
                    ['%parameter%' => 'maxIntegerValue'],
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
                    'minIntegerValue' => 12345,
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
