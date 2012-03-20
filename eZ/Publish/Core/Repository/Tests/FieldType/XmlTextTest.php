<?php
/**
 * File containing the FieldType\XmlTextTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\XmlText\Type as XmlTextType,
    eZ\Publish\Core\Repository\FieldType\XmlText\Value as XmlTextValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject,
    ReflectionProperty,
    DOMDocument;

/**
 * @group fieldType
 */
class XmlTextTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @group dateTime
     * @covers \eZ\Publish\Core\Repository\FieldType::allowedSettings
     */
    public function testAllowedSettings()
    {
        $ft = new XmlTextType( $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XMLText\\Input\\Parser' ) );
        self::assertSame(
            array( 'numRows', 'tagPreset', 'defaultText' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidType()
    {
        $ft = new XmlTextType( $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XMLText\\Input\\Parser' ) );
        $ft->acceptValue( $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @dataProvider providerForTestAcceptValueInvalidFormat
     */
    public function testAcceptValueInvalidFormat( $text, $format )
    {
        $parserMock = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XMLText\\Input\\Parser' );
        $parserMock->expects( $this->once() )
                    ->method( 'process' )
                    ->with( $text )
                    ->will( $this->returnValue( $text ) );
        $ft = new XmlTextType( $parserMock );
        $value = $ft->buildValue( $text, $format );
        $ft->acceptValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueValidFormat
     */
    public function testAcceptValueValidFormat( $text, $format )
    {
        $parserMock = $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XMLText\\Input\\Parser' );
        $parserMock->expects( $this->once() )
                    ->method( 'process' )
                    ->with( $text )
                    ->will( $this->returnValue( new DOMDocument( '1.0', 'utf-8' ) ) );
        $ft = new XmlTextType( $parserMock );
        $value = $ft->buildValue( $text, $format );
        $ft->acceptValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\XmlText\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        // @todo Do one per value class
        $ft = new XmlTextType( $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\XMLText\\Input\\Parser' ) );
        $value = $ft->buildValue( '', XmlTextValue::INPUT_FORMAT_PLAIN );

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
