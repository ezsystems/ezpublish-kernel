<?php
/**
 * File containing the EmailAddressValueValidatorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\EmailAddress\Value as EmailAddressValue;
use eZ\Publish\Core\FieldType\Validator\EmailAddressValidator;

/**
 *
 * @todo add more tests, like on validateConstraints method
 * @group fieldType
 * @group validator
 */
class EmailAddressValidatorTest extends FieldTypeTest
{

    /**
     * This test ensure an EmailAddressValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new EmailAddressValidator
        );
    }

    /**
     * Tests setting and getting constraints
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::initializeWithConstraints
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsInitializeGet()
    {
        $constraints = array(
            "Extent" => "regex",
        );
        $validator = new EmailAddressValidator;
        $validator->initializeWithConstraints(
            $constraints
        );
        $this->assertSame( $constraints["Extent"], $validator->Extent );

    }

    /**
     * Test getting constraints schema
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::getConstraintsSchema
     */
    public function testGetConstraintsSchema()
    {
        $constraintsSchema = array(
            "Extent" => array(
                "type" => "string",
                "default" => "regex"
            ),
        );
        $validator = new EmailAddressValidator;
        $this->assertSame( $constraintsSchema, $validator->getConstraintsSchema() );
    }

    /**
     * Tests setting and getting constraints
     *
     * @covers \eZ\Publish\Core\FieldType\Validator::__set
     * @covers \eZ\Publish\Core\FieldType\Validator::__get
     */
    public function testConstraintsSetGet()
    {
        $constraints = array(
            "Extent" => "regex",
        );
        $validator = new EmailAddressValidator;
        $validator->Extent = $constraints["Extent"];
        $this->assertSame( $constraints["Extent"], $validator->Extent );
    }

    public function testValidateCorrectEmailAddresses()
    {
        $validator = new EmailAddressValidator;
        $validator->Extent = 'regex';
        $emailAddresses = array( 'john.doe@example.com', 'Info@eZ.No' );
        foreach ( $emailAddresses as $value )
        {
            $this->assertTrue( $validator->validate( new EmailAddressValue( $value ) ) );
            $this->assertSame( array(), $validator->getMessage() );
        }

    }

    /**
     * Tests validating a wrong value
     *
     * @covers \eZ\Publish\Core\FieldType\Validator\EmailAddressValidator::validate
     */
    public function testValidateWrongEmailAddresses( )
    {
        $validator = new EmailAddressValidator;
        $validator->Extent = "regex";
        $emailAddresses = array( '.john.doe@example.com', 'Info-at-eZ.No' );
        foreach ( $emailAddresses as $value )
        {
            $this->assertFalse( $validator->validate( new EmailAddressValue( $value ) ) );
        }
    }
}
