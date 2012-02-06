<?php
/**
 * File containing the FieldTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\User,
    eZ\Publish\Core\Repository\FieldType\Value as FieldValue,
    eZ\Publish\Core\Repository\FieldType\TextLine\Value as TextLineValue,
    eZ\Publish\Core\Repository\FieldType\TextLine\StringLengthValidator,
    eZ\Publish\Core\Repository\FieldType\Keyword\Value as KeywordValue,
    ReflectionObject;

/**
 * Test case for ezp\Content\Field
 */
class FieldTest extends BaseContentTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->fields = $this->content->getCurrentVersion()->getFields();
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::getVersion
     */
    public function testVersion()
    {
        foreach ( $this->fields as $identifier => $field )
        {
            self::assertInstanceOf( 'ezp\\Content\\Version', $field->getVersion() );
            self::assertSame( $field->getVersion(), $field->version );
        }
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::getFieldDefinition
     */
    public function testFieldDefinition()
    {
        foreach ( $this->fields as $identifier => $field )
        {
            self::assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $field->getFieldDefinition() );
            self::assertSame( $field->getFieldDefinition(), $field->fieldDefinition );
        }
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::__construct
     */
    public function testFieldsAreValid()
    {
        foreach ( $this->fields as $identifier => $field )
        {
            self::assertSame( $field->fieldDefinition->identifier, $identifier );
            self::assertSame( $field->fieldDefinition->id, $field->fieldDefinitionId );
            self::assertSame( $field->fieldDefinition->fieldType, $field->type );
            self::assertSame( $field->version->versionNo, $field->versionNo );
            self::assertSame( $field->fieldDefinition->defaultValue, $field->value );
            self::assertSame( $field->value, $field->fieldDefinition->type->getValue() );
        }
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::__construct
     */
    public function testGetValue()
    {
        foreach ( $this->fields as $identifier => $field )
        {
            self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value', $field->getValue() );
            self::assertSame( $field->getValue(), $field->value );
        }
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::setValue
     */
    public function testSetValue()
    {
        $pulpFictionQuote = 'The path of the righteous man is beset on all sides by the iniquities of the selfish and the tyranny of evil men.';
        $value = new TextLineValue( $pulpFictionQuote );
        $field = $this->fields['title'];
        $field->setValue( $value );
        self::assertSame( $value, $field->getValue() );
        self::assertsame( $value, $field->fieldDefinition->type->getValue() );
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::validateValue
     */
    public function testSetValueWithValidator()
    {
        $pulpFictionQuote = 'You think water moves fast? You should see ice. It moves like it has a mind.';
        $value = new TextLineValue( $pulpFictionQuote );
        $validator = new StringLengthValidator();
        $validator->maxStringLength = 100;
        $field = $this->fields['title'];
        $field->fieldDefinition->setValidator( $validator );
        $field->setValue( $value );
    }

    /**
     * @group field
     * @group content
     * @covers \ezp\Content\Field::validateValue
     * @expectedException \ezp\Base\Exception\FieldValidation
     */
    public function testSetInvalidValueWithValidator()
    {
        $longPulpFictionQuote = <<<EOT
The path of the righteous man is beset on all sides by the iniquities of the selfish and the tyranny of evil men.
Blessed is he who, in the name of charity and good will, shepherds the weak through the valley of darkness,
for he is truly his brother's keeper and the finder of lost children.

And I will strike down upon thee with great vengeance and furious anger those who would attempt to poison and destroy My brothers.
And you will know My name is the Lord when I lay My vengeance upon thee.
EOT;
        $value = new TextLineValue( $longPulpFictionQuote );
        $validator = $this->getMockForAbstractClass( 'eZ\\Publish\\Core\\Repository\\FieldType\\Validator' );
        $validator
            ->expects( $this->once() )
            ->method( 'validate' )
            ->with( $value )
            ->will( $this->returnValue( false ) );

        $field = $this->fields['title'];
        $fieldType = $field->getFieldDefinition()->getType();
        $refType = new ReflectionObject( $fieldType );
        $refAllowedValidators = $refType->getProperty( 'allowedValidators' );
        $refAllowedValidators->setAccessible( true );
        $refAllowedValidators->setValue( $fieldType, array( get_class( $validator ) ) );

        $field->fieldDefinition->setValidator( $validator );
        $field->setValue( $value );
    }
}
