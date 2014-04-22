<?php
/**
 * File containing the FieldType\RichTextTypeTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\RichText\Type as RichTextType;
use eZ\Publish\Core\FieldType\RichText\Value;
use eZ\Publish\Core\FieldType\RichText\ConverterDispatcher;
use eZ\Publish\Core\FieldType\RichText\ValidatorDispatcher;
use eZ\Publish\Core\FieldType\RichText\Validator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Relation;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @group fieldType
 * @group ezrichtext
 */
class RichTextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \eZ\Publish\Core\FieldType\RichText\Type
     */
    protected function getFieldType()
    {
        $fieldType = new RichTextType(
            new ConverterDispatcher( array( "http://docbook.org/ns/docbook" => null ) ),
            new ValidatorDispatcher(
                array(
                    "http://docbook.org/ns/docbook" => new Validator(
                        array(
                            $this->getAbsolutePath( "eZ/Publish/Core/FieldType/RichText/Resources/schemas/docbook/ezpublish.rng" ),
                            $this->getAbsolutePath( "eZ/Publish/Core/FieldType/RichText/Resources/schemas/docbook/docbook.iso.sch.xsl" )
                        )
                    )
                )
            )
        );
        $fieldType->setTransformationProcessor( $this->getTransformationProcessorMock() );

        return $fieldType;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransformationProcessorMock()
    {
        return $this->getMockForAbstractClass(
            "eZ\\Publish\\Core\\Persistence\\TransformationProcessor",
            array(),
            '',
            false,
            true,
            true
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getValidatorConfigurationSchema
     */
    public function testValidatorConfigurationSchema()
    {
        $fieldType = $this->getFieldType();
        self::assertEmpty(
            $fieldType->getValidatorConfigurationSchema(),
            "The validator configuration schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\FieldType::getSettingsSchema
     */
    public function testSettingsSchema()
    {
        $fieldType = $this->getFieldType();
        self::assertSame(
            array(
                "numRows" => array(
                    "type" => "int",
                    "default" => 10
                ),
                "tagPreset" => array(
                    "type" => "choice",
                    "default" => RichTextType::TAG_PRESET_DEFAULT
                ),
            ),
            $fieldType->getSettingsSchema(),
            "The settings schema does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValueInvalidType()
    {
        $this->getFieldType()->acceptValue( $this->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Value' )->disableOriginalConstructor()->getMock() );
    }

    public static function providerForTestAcceptValueValidFormat()
    {
        return array(

            array(
                $xml = '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
</section>
'
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueValidFormat
     */
    public function testAcceptValueValidFormat( $input )
    {
        $fieldType = $this->getFieldType();
        $fieldType->acceptValue( $input );
    }

    public static function providerForTestAcceptValueInvalidFormat()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">
  <h1>This is a heading.</h1>
</section>',
                new InvalidArgumentException(
                    "\$inputValue",
                    "Validation of XML content failed: Error in 3:0: Element section has extra content: h1"
                )
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink">
  <title>This is a heading.</title>
</section>',
                new InvalidArgumentException(
                    "\$inputValue",
                    "Validation of XML content failed: /*[local-name()='section' and namespace-uri()='http://docbook.org/ns/docbook']: The root element must have a version attribute."
                )
            ),
            array(
                'This is not XML at all!',
                new InvalidArgumentException(
                    "\$inputValue",
                    "Could not create XML document: Start tag expected, '<' not found"
                )
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?><unknown xmlns="http://www.w3.org/2013/foobar"><format /></unknown>',
                new NotFoundException(
                    "Validator",
                    "http://www.w3.org/2013/foobar"
                )
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Author\Type::acceptValue
     * @dataProvider providerForTestAcceptValueInvalidFormat
     */
    public function testAcceptValueInvalidFormat( $input, Exception $expectedException )
    {
        try
        {
            $fieldType = $this->getFieldType();
            $fieldType->acceptValue( $input );
            $this->fail( "An InvalidArgumentException was expected! None thrown." );
        }
        catch ( InvalidArgumentException $e )
        {
            $this->assertEquals( $expectedException->getMessage(), $e->getMessage() );
        }
        catch ( NotFoundException $e )
        {
            $this->assertEquals( $expectedException->getMessage(), $e->getMessage() );
        }
        catch ( Exception $e )
        {
            $this->fail(
                "Unexpected exception thrown! " . get_class( $e ) . " thrown with message: " . $e->getMessage()
            );
        }
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
</section>
';

        $fieldType = $this->getFieldType();
        $fieldValue = $fieldType->toPersistenceValue( $fieldType->acceptValue( $xmlString ) );

        self::assertInternalType( 'string', $fieldValue->data );
        self::assertSame( $xmlString, $fieldValue->data );
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\Type::getName
     * @dataProvider providerForTestGetName
     */
    public function testGetName( $xmlString, $expectedName )
    {
        $value = new Value( $xmlString );

        $fieldType = $this->getFieldType();
        $this->assertEquals(
            $expectedName,
            $fieldType->getName( $value )
        );
    }

    /**
     * @todo format does not really matter for the method tested, but the fixtures here should be replaced
     * by valid docbook anyway
     */
    public static function providerForTestGetName()
    {
        return array(

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1">This is a piece of text</header></section>',
                "This is a piece of text"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1">This is a piece of <emphasize>text</emphasize></header></section>',
                /** @todo FIXME: should probably be "This is a piece of text" */
                "This is a piece of"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1"><strong>This is a piece</strong> of text</header></section>',
                /** @todo FIXME: should probably be "This is a piece of text" */
                "This is a piece"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><header level="1"><strong><emphasize>This is</emphasize> a piece</strong> of text</header></section>',
                /** @todo FIXME: should probably be "This is a piece of text" */
                "This is"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph><table class="default" border="0" width="100%" custom:summary="wai" custom:caption=""><tr><td><paragraph>First cell</paragraph></td><td><paragraph>Second cell</paragraph></td></tr><tr><td><paragraph>Third cell</paragraph></td><td><paragraph>Fourth cell</paragraph></td></tr></table></paragraph><paragraph>Text after table</paragraph></section>',
                /** @todo FIXME: should probably be "First cell" */
                "First cellSecond cell"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><ul><li><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">List item</paragraph></li></ul></paragraph></section>',
                "List item"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><ul><li><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">List <emphasize>item</emphasize></paragraph></li></ul></paragraph></section>',
                "List item"
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" />',
                ""
            ),

            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
         xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
         xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph><strong><emphasize>A simple</emphasize></strong> paragraph!</paragraph></section>',
                "A simple"
            ),

            array( '<section><paragraph>test</paragraph></section>', "test" ),

            array( '<section><paragraph><link node_id="1">test</link><link object_id="1">test</link></paragraph></section>', "test" ),
        );
    }

    /**
     * @todo handle embeds when implemented
     * @covers \eZ\Publish\Core\FieldType\RichText\Type::getRelations
     */
    public function testGetRelations()
    {
        $xml =
<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para><link xlink:href="ezlocation://72">link1</link></para>
    <para><link xlink:href="ezlocation://61">link2</link></para>
    <para><link xlink:href="ezlocation://61">link3</link></para>
    <para><link xlink:href="ezcontent://70">link4</link></para>
    <para><link xlink:href="ezcontent://75">link5</link></para>
    <para><link xlink:href="ezcontent://75">link6</link></para>
</section>
EOT;

        $fieldType = $this->getFieldType();
        $this->assertEquals(
            array(
                Relation::LINK => array(
                    "locationIds" => array( 72, 61 ),
                    "contentIds" => array( 70, 75 ),
                ),
                Relation::EMBED => array(
                    "locationIds" => array(),
                    "contentIds" => array(),
                ),
            ),
            $fieldType->getRelations( $fieldType->acceptValue( $xml ) )
        );
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    protected function getAbsolutePath( $relativePath )
    {
        return self::getInstallationDir() . "/" . $relativePath;
    }

    /**
     * @return string
     */
    static protected function getInstallationDir()
    {
        static $installDir = null;
        if ( $installDir === null )
        {
            $config = require 'config.php';
            $installDir = $config['install_dir'];
        }
        return $installDir;
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezrichtext';
    }

    public function provideDataForGetName()
    {
        return array();
    }
}
