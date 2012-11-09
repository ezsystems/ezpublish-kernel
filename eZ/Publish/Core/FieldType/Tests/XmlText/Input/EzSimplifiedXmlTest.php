<?php
/**
 * File containing the XmlText EzSimplifiedXml test
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Input;

use eZ\Publish\Core\FieldType\XmlText\Input\EzSimplifiedXml,
    PHPUnit_Framework_TestCase,
    Exception;

class EzSimplifiedXmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerForTestConvertCorrect
     */
    public function testConvertCorrect( $xmlString, $expectedXml )
    {
        $input = new EzSimplifiedXml( $xmlString );
        $this->assertEquals( $expectedXml, trim( $input->getInternalRepresentation() ) );
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
            $input = new EzSimplifiedXml( $xmlString );
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
                "Argument 'xmlString' is invalid: Validation of XML content failed: Element 'wrongTag': This element is not expected. Expected is one of ( section, p, para, h, h1, h2, h3, h4, h5, h6 ).",
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?><section><p wrongAttribute="foo">Some content</p>
<p>
<table><tr></tr></table>
<a node_id="abc"><a object_id="123">This is a link</a></a>
</p>
</section>',
                "Argument 'xmlString' is invalid: Validation of XML content failed: Element 'p', attribute 'wrongAttribute': The attribute 'wrongAttribute' is not allowed.
Element 'tr': Missing child element(s). Expected is one of ( th, td ).
Element 'a', attribute 'node_id': 'abc' is not a valid value of the atomic type 'xs:integer'.
Element 'a': This element is not expected. Expected is one of ( custom, b, bold, i, em, embed, embed-inline ).",
            ),
        );
    }
}
