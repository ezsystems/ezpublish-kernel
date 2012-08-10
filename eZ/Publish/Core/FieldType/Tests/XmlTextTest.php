<?php
/**
 * File containing the FieldType\XmlTextTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\XmlText\Type as XmlTextType,
    eZ\Publish\Core\FieldType\XmlText\Value as XmlTextValue,
    DOMDocument;

/**
 * @group fieldType
 * @group ezxmltext
 */
class XmlTextTypeTest extends FieldTypeTest
{
    /**
     * Normally this should be enough:
     *
     * $ft = new XmlTextType( $this->getMock( 'eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser' ) );
     *
     * But there is a bug in PHPUnit when mocking an interface and calling the test in a certain way
     * (eg. with --group switch), when invocationMocker is missing.
     *
     * Possibly described here:
     * https://github.com/sebastianbergmann/phpunit-mock-objects/issues/26
     */
    protected function getFieldType()
    {
        return new XmlTextType(
            new \eZ\Publish\Core\FieldType\XmlText\Input\Parser\Raw(
                new \eZ\Publish\Core\FieldType\XmlText\Schema()
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $ft = $this->getFieldType();
        self::assertEmpty(
            $ft->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $ft = $this->getFieldType();
        self::assertSame(
            array(
                "numRows" => array(
                    "type" => "int",
                    "default" => 10
                ),
                "tagPreset" => array(
                    "type" => "choice",
                    "default" => XmlTextType::TAG_PRESET_DEFAULT
                ),
            ),
            $ft->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\XmlText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidType()
    {
        $ft = $this->getFieldType();
        $ft->acceptValue( $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Value' ) );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\XmlText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @dataProvider providerForTestAcceptValueInvalidFormat
     */
    public function testAcceptValueInvalidFormat( $text, $format )
    {
        self::markTestIncomplete( "buildValue changed w/o this test being changed as well" );
        $parserMock = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser' );
        $parserMock->expects( $this->once() )
                    ->method( 'process' )
                    ->with( $text )
                    ->will( $this->returnValue( $text ) );
        $ft = new XmlTextType( $parserMock );
        $value = $ft->buildValue( $text, $format );
        $ft->acceptValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueValidFormat
     */
    public function testAcceptValueValidFormat( $text, $format )
    {
        self::markTestIncomplete( "buildValue changed w/o this test being changed as well" );
        $parserMock = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser' );
        $parserMock->expects( $this->once() )
                    ->method( 'process' )
                    ->with( $text )
                    ->will( $this->returnValue( new DOMDocument( '1.0', 'utf-8' ) ) );
        $ft = new XmlTextType( $parserMock );
        $value = $ft->buildValue( $text, $format );
        $ft->acceptValue( $value );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\XmlText\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        self::markTestIncomplete( "buildValue changed w/o this test being changed as well" );
        // @todo Do one per value class
        $ft = $this->getFieldType();
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
