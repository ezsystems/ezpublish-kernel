<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\EmailAddress\Value as EmailAddressValue;
use eZ\Publish\Core\FieldType\Validator\EmailAddressValidator;
use eZ\Publish\Core\FieldType\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @todo add more tests, like on validateConstraints method
 * @group fieldType
 * @group validator
 */
class EmailAddressValidatorTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(
            Validator::class,
            new EmailAddressValidator()
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
            'Extent' => 'regex',
        ];
        $validator = new EmailAddressValidator();
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame($constraints['Extent'], $validator->Extent);
    }

    /**
     * Test getting constraints schema.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = [
            'Extent' => [
                'type' => 'string',
                'default' => 'regex',
            ],
        ];
        $validator = new EmailAddressValidator();
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
            'Extent' => 'regex',
        ];
        $validator = new EmailAddressValidator();
        $validator->Extent = $constraints['Extent'];
        $this->assertSame($constraints['Extent'], $validator->Extent);
    }

    public function testValidateCorrectEmailAddresses()
    {
        $validator = new EmailAddressValidator();
        $validator->Extent = 'regex';
        $emailAddresses = ['john.doe@example.com', 'Info@eZ.No'];
        foreach ($emailAddresses as $value) {
            $this->assertTrue($validator->validate(new EmailAddressValue($value)));
            $this->assertSame([], $validator->getMessage());
        }
    }

    /**
     * Tests validating a wrong value.
     *
     * @covers \eZ\Publish\Core\FieldType\Validator\EmailAddressValidator::validate
     */
    public function testValidateWrongEmailAddresses()
    {
        $validator = new EmailAddressValidator();
        $validator->Extent = 'regex';
        $emailAddresses = ['.john.doe@example.com', 'Info-at-eZ.No'];
        foreach ($emailAddresses as $value) {
            $this->assertFalse($validator->validate(new EmailAddressValue($value)));
        }
    }
}
