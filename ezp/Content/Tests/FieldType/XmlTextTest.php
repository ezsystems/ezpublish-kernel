<?php
/**
 * File containing the XmlText\FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Factory,
    eZ\Publish\Core\Repository\FieldType\XmlText\Type as XmlTextType,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\FieldType\XmlText\Value as XmlTextValue,
    ezp\Base\Exception\BadFieldTypeInput,
    PHPUnit_Framework_TestCase,
    ReflectionObject, ReflectionProperty;

/**
 * @group fieldType
 */
class XmlTextTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will make sure a correct mapping for the field type string has
     * been made.
     *
     * @covers \eZ\Publish\Core\Repository\FieldType\Factory::build
     */
    public function testFactory()
    {
        self::assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\FieldType\\XmlText\\Type",
            Factory::build( "ezxmltext" ),
            "XmlText object not returned for 'ezxmltext'. Incorrect mapping?"
        );
    }

    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testAllowedSettings()
    {
        $ft = new XmlTextType();
        self::assertSame(
            array( 'numRows', 'tagPreset', 'defaultText' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::acceptValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testAcceptValueInvalidType()
    {
        $ft = new XmlTextType;
        $ft->setValue( $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::acceptValue
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @dataProvider providerForTestAcceptValueInvalidFormat
     */
    public function testAcceptValueInvalidFormat( $text, $format )
    {
        $ft = new XmlTextType;
        $value = new XmlTextValue( $text, $format );
        $ft->setValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueValidFormat
     */
    public function testAcceptValueValidFormat( $text, $format )
    {
        $ft = new XmlTextType;
        $value = new XmlTextValue( $text, $format );
        $ft->setValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        // @todo Do one per value class
        $value = new XmlTextValue( '', XmlTextValue::INPUT_FORMAT_PLAIN );

        $ft = new XmlTextType();

        $fieldValue = $ft->toPersistenceValue( $value );

        self::assertSame( "", $fieldValue->data );
    }

    public function providerForTestAcceptValueInvalidFormat()
    {
        return array(

            // RawValue requires root XML + section tags
            array( '', XmlTextValue::INPUT_FORMAT_RAW ),

            // wrong closing tag
            array( '<a href="http://www.google.com/">bar</foo>', XmlTextValue::INPUT_FORMAT_PLAIN ),
        );
    }

    public static function providerForTestAcceptValueValidFormat()
    {
        return array(

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1">This is a piece of text</header></section>',
                XmlTextValue::INPUT_FORMAT_RAW ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />',
                XmlTextValue::INPUT_FORMAT_RAW ),

            array( '<section>test</section>', XmlTextValue::INPUT_FORMAT_PLAIN ),

            array( '<paragraph><a href="eznode://1">test</a><a href="ezobject://1">test</a></paragraph>', XmlTextValue::INPUT_FORMAT_PLAIN ),
        );
    }
}
