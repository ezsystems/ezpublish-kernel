<?php
/**
 * File containing the XmlText\FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType\XmlText;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\XmlText\Type as XmlTextType,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Content\FieldType\XmlText\Value as RawValue,
    ezp\Content\FieldType\XmlText\Value\OnlineEditor as OnlineEditorValue,
    ezp\Content\FieldType\XmlText\Value\Simplified as SimplifiedValue,
    ezp\Base\Exception\BadFieldTypeInput,
    PHPUnit_Framework_TestCase,
    ReflectionObject, ReflectionProperty;

class FieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\XmlText\\Type",
            Factory::build( "ezxmltext" ),
            "XmlText object not returned for 'ezxmltext', incorrect mapping? "
        );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::canParseValue
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     * @dataProvider providerForTestCanParseSimplifiedValueInvalidFormat
     * @group fieldType
     */
    public function testCanParseSimplifiedValueInvalidFormat( $value )
    {
        $ft = new XmlTextType();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $refMethod->invoke( $ft, $value );
    }

    public function providerForTestCanParseSimplifiedValueInvalidFormat()
    {
        return array(
            // RawValue requires root XML + section tags
            array( new RawValue( '' ) ),
            // wrong closing tag
            array( new SimplifiedValue( '<a href="http://www.google.com/">bar</foo>' ) ),
        );
    }

    /**
     * @param BaseValue $value
     * @covers \ezp\Content\FieldType\XmlText\Type::canParseValue
     * @group fieldType
     * @dataProvider providerForTestCanParseValueValidFormat
     */
    public function testCanParseValueValidFormat( BaseValue $value )
    {
        $ft = new XmlTextType();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    public static function providerForTestCanParseValueValidFormat()
    {
        return array(

            array( new RawValue( '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1">This is a piece of text</header></section>' ) ),

            array( new RawValue( '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />' ) ),

            array( new SimplifiedValue( '<section>test</section>' ) ),

            array( new SimplifiedValue( '<paragraph><a href="eznode://1">test</a><a href="ezobject://1">test</a></paragraph>' ) )
    );
    }

    /**
     * @param BaseValue $value
     * @covers \ezp\Content\FieldType\XmlText\Type::canParseValue
     * @group fieldType
     * @dataProvider providerForTestCanParseValueInvalidFormat
     * @expectedException ezp\Base\Exception\BadFieldTypeInput
     */
    public function testCanParseValueInvalidFormat( BaseValue $value )
    {
        $ft = new XmlTextType();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "canParseValue" );
        $refMethod->setAccessible( true );

        $refMethod->invoke( $ft, $value );
    }

    public static function providerForTestCanParseValueInvalidFormat()
    {
        return array(
            array( new RawValue( '' ) ),
            array( new RawValue( '<?xml version="1.0" encoding="utf-8"?>' ) ),
            array( new RawValue( '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">' ) ),
        );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::toFieldValue
     * @group fieldType
     */
    public function testToFieldValue()
    {
        // @todo Do one per value class
        $value = new SimplifiedValue( '');

        $ft = new XmlTextType();
        $ft->setValue( $value );

        $fieldValue = $ft->toFieldValue();

        self::assertSame( $value, $fieldValue->data );
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Value::__construct
     * @group fieldType
     */
    public function testBuildFieldValueWithParam()
    {
        self::markTestIncomplete();
        /*$countries = array( "Belgium", "Norway" );
        $value = new CountryValue( $countries );
        self::assertSame( $countries, $value->values );*/
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Value::fromString
     * @group fieldType
     */
    public function testBuildFieldValueFromString()
    {
        self::markTestIncomplete();
        /*$country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Country\\Value", $value );
        self::assertSame( array( $country ), $value->values );*/
    }

    /**
     * @covers \ezp\Content\XmlText\Country\Value::__toString
     * @group fieldType
     */
    public function testFieldValueToString()
    {
        self::markTestIncomplete();
        /*$country = "Belgium";
        $value = CountryValue::fromString( $country );
        self::assertSame( $country, (string)$value );

        self::assertSame(
            array( $country ),
            CountryValue::fromString( (string)$value )->values,
            "fromString() and __toString() must be compatible"
        );*/
    }

    /**
     * @covers \ezp\Content\FieldType\XmlText\Type::getInputHandler
     * @dataProvider providerForTestGetInputHandler
     */
    public function testGetInputHandler( $value, $parserClass )
    {
        $type = new XmlTextType();
        $typeReflection = new ReflectionObject( $type );
        $getInputHandlerReflection = $typeReflection->getMethod( "getInputHandler" );
        $getInputHandlerReflection->setAccessible( true );

        $handler = $getInputHandlerReflection->invoke( $type, $value );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\XmlText\\Input\\Handler', $handler );

        $handlerReflection = new ReflectionProperty( $handler, 'parser' );
        $handlerReflection->setAccessible( true );
        self::assertInstanceOf( $parserClass, $handlerReflection->getValue( $handler ) );
    }

    public function providerForTestGetInputHandler()
    {
        return array(
            array( new RawValue, 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\Raw' ),
            array( new SimplifiedValue, 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\Simplified' ),
            array( new OnlineEditorValue, 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\OnlineEditor' )
        );
    }

    public function testGetSortInfo()
    {
        $type = new XmlTextType();
        $ref = new ReflectionObject( $type );
        $refMethod = $ref->getMethod( "getSortInfo" );
        $refMethod->setAccessible( true );

        self::assertFalse( $refMethod->invoke( $type ) );
    }

    /**
     * Tests that onContentPublish will convert any Value it receives as input to a Raw value
     */
    public function testOnContentPublish()
    {
        $value = new SimplifiedValue( '' );
        $xmlString = '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"/>
';

        $type = $this
            ->getMockBuilder( 'ezp\\Content\\FieldType\\XmlText\\Type' )
            ->setMethods( array( 'getInputHandler' ) )
            ->getMock();

        $parser = $this->getMock( 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\Simplified' );
        $handler = $this->getMockBuilder( 'ezp\\Content\\FieldType\\XmlText\\Input\\Handler' )
            ->setConstructorArgs( array( $parser ) )
            ->getMock();

        $repository = $this
            ->getMockBuilder( 'ezp\\Base\\Repository' )
            ->disableOriginalConstructor()
            ->getMock();

        $field = $this
            ->getMockBuilder( 'ezp\\Content\\Field' )
            ->disableOriginalConstructor()
            ->getMock();

        $type
            ->expects( $this->once() )
            ->method( 'getInputHandler' )
            ->with( $value )
            ->will( $this->returnValue( $handler ) );

        $handler
            ->expects( $this->any() )
            ->method( 'getDocumentAsXml' )
            ->will( $this->returnValue( $xmlString ) );

        $field
            ->expects( $this->at( 0 ) )
            ->method( '__get' )
            ->with( 'value' )
            ->will( $this->returnValue( $value ) );

        $field
            ->expects( $this->at( 1 ) )
            ->method( '__get' )
            ->with( 'version' )
            ->will( $this->returnValue( $this->getMock( 'ezp\\Content\\Version' ) ) );


        $type->onContentPublish( $repository, $field );

        self::assertEquals( $type->value, new RawValue( $xmlString ) );
    }
}
