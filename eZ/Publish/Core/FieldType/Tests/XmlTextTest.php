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
    eZ\Publish\Core\FieldType\XmlText\Input\Parser\Raw as RawXmlTextInputParser,
    eZ\Publish\Core\FieldType\XmlText\Schema as XmlTextSchema,
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
        return new XmlTextType( new RawXmlTextInputParser( new XmlTextSchema ) );
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
        $this->getFieldType()->acceptValue( $this->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Value' )->disableOriginalConstructor()->getMock() );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\XmlText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @dataProvider providerForTestAcceptValueInvalidFormat
     */
    public function testAcceptValueInvalidFormat( $text, $parser )
    {
        $parserMock = $this->getMockBuilder( $parser )->disableOriginalConstructor()->getMock();
        $parserMock
            ->expects( $this->once() )
            ->method( 'process' )
            ->with( $text )
            ->will( $this->throwException( $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Exceptions\\InvalidArgumentException" ) ) );
        $fieldType = new XmlTextType( $parserMock );
        $fieldType->acceptValue( $text );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueValidFormat
     */
    public function testAcceptValueValidFormat( $text, $parser )
    {
        $parserMock = $this->getMockBuilder( $parser )->disableOriginalConstructor()->getMock();
        $parserMock
            ->expects( $this->once() )
            ->method( 'process' )
            ->with( $text )
            ->will( $this->returnValue( new DOMDocument( '1.0', 'utf-8' ) ) );
        $fieldType = new XmlTextType( $parserMock );
        $fieldType->acceptValue( $text );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\XmlText\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $xmlData = '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1">Header 1</header></section>';
        // @todo Do one per value class
        $ft = $this->getFieldType();
        $value = $ft->acceptValue( $xmlData );

        $fieldValue = $ft->toPersistenceValue( $value );

        self::assertSame( $xmlData, $fieldValue->data );
    }

    public function providerForTestAcceptValueInvalidFormat()
    {
        return array(
            // RawValue requires root XML + section tags
            array( '', "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Raw" ),

            // wrong closing tag
            array( '<a href="http://www.google.com/">bar</foo>', "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Simplified" ),
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
                "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Raw"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />',
                "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Raw"
            ),

            array( '<section>test</section>', "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Simplified" ),

            array( '<paragraph><a href="eznode://1">test</a><a href="ezobject://1">test</a></paragraph>', "eZ\\Publish\\Core\\FieldType\\XmlText\\Input\\Parser\\Simplified" ),
        );
    }
}
