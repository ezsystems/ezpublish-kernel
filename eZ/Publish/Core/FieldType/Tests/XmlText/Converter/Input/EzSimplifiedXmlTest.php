<?php
/**
 * File containing the XmlText EzSimplifiedXml Converter test
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter\Input;

use eZ\Publish\Core\FieldType\XmlText\Converter\Input\EzSimplifiedXml as Converter,
    PHPUnit_Framework_TestCase,
    Exception;

class EzSimplifiedXmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter\Input
     */
    private $converter;

    public function setUp()
    {
        $this->converter = new Converter(
            __DIR__ . "/../../../../../../../Bundle/EzPublishCoreBundle/Resources/schemas/ezsimplifiedxml.xsd",
            __DIR__ . "/../../../../../../../Bundle/EzPublishCoreBundle/Resources/stylesheets/eZSimplifiedXml2eZXml.xsl"
        );
    }

    /**
     * @dataProvider providerForTestConvertCorrect
     */
    public function testConvertCorrect( $xmlString, $expectedXml )
    {
        $this->assertEquals( $expectedXml, trim( $this->converter->convert( $xmlString ) ) );
    }

    public function providerForTestConvertCorrect()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/">
<p>&lt;test&gt;</p><h>title</h>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/">
<paragraph>&lt;test&gt;</paragraph><header>title</header>
</section>',
            ),
        );
    }

    /**
     * @dataProvider providerForTestConvertIncorrect
     */
    public function testConvertIncorrect( $xmlString, $exceptionMessage )
    {
        try
        {
            $this->converter->convert( $xmlString );
        }
        catch ( Exception $e )
        {
            $this->assertEquals( $exceptionMessage, $e->getMessage() );
            return;
        }

        $this->fail( "Expecting an Exception with message: " . $exceptionMessage );
    }

    public function providerForTestConvertIncorrect()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?><section><wrongTag/></section>',
                "Element 'wrongTag': This element is not expected. Expected is one of ( section, p, para, h, h1, h2, h3, h4, h5, h6 ).",
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?><section><p wrongAttribute="foo">Some content</p>
<p>
<table><tr></tr></table>
<a node_id="abc"><a object_id="123">This is a link</a></a>
</p>
</section>',
                "Element 'p', attribute 'wrongAttribute': The attribute 'wrongAttribute' is not allowed.
Element 'tr': Missing child element(s). Expected is one of ( th, td ).
Element 'a', attribute 'node_id': 'abc' is not a valid value of the atomic type 'xs:integer'.
Element 'a': This element is not expected. Expected is one of ( custom, b, bold, i, em, embed, embed-inline ).",
            ),
        );
    }
}
