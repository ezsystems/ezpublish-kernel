<?php
/**
 * File containing the Html5 converter test
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\Html5;
use PHPUnit_Framework_TestCase;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Tests the Html5 converter
 */
class Html5Test extends PHPUnit_Framework_TestCase
{

    protected function getDefaultStylesheet()
    {
        return __DIR__ . '../../../../XmlText/Input/Resources/stylesheets/eZXml2Html5_core.xsl';
    }

    protected function getPreConvertMock()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\FieldType\XmlText\Converter' )
            ->getMock();
    }

    public function dataProviderConstructorException()
    {
        return array(
            array(
                array( 1, 2 ),
                array( 1, $this->getPreConvertMock() ),
                array( $this->getPreConvertMock(), 1 )
            )
        );
    }

    /**
     * @dataProvider dataProviderConstructorException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testConstructorException( array $preConverters )
    {
        new Html5( '', array(), $preConverters );
    }

    public function testPreConverterCalled()
    {
        $dom = new DOMDocument();
        $preConverterMock1 = $this->getPreConvertMock();
        $preConverterMock2 = $this->getPreConvertMock();

        $preConverterMock1->expects( $this->once() )
            ->method( 'convert' )
            ->with( $this->equalTo( $dom ) );

        $preConverterMock2->expects( $this->once() )
            ->method( 'convert' )
            ->with( $this->equalTo( $dom ) );

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                $preConverterMock1,
                $preConverterMock2
            )
        );
        $html5->convert( $dom );
    }

    public function dataProviderAnchor()
    {
        $that = $this;
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><anchor name="start"/>This is the start</paragraph></section>',
                '//a[@id="start"]',
                function ( DOMNodeList $xpathResult ) use ( $that )
                {
                    $that->assertEquals( $xpathResult->length, 1 );
                    $anchor = $xpathResult->item( 0 );
                    $that->assertEquals( $anchor->parentNode->localName, 'p' );
                }
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/">
    <paragraph><anchor name="start"/>This is<anchor name="middle"/> the start<anchor name="end"/></paragraph>
</section>',
                '//a[@id]',
                function ( DOMNodeList $xpathResult ) use ( $that )
                {
                    $ids = array( 'start', 'middle', 'end' );
                    $that->assertEquals( $xpathResult->length, count( $ids ) );
                    foreach ( $xpathResult as $k => $anchor )
                    {
                        $that->assertEquals(
                            $anchor->getAttribute( 'id' ),
                            $ids[$k]
                        );
                        $that->assertEquals( $anchor->parentNode->localName, 'p' );
                    }
                }
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">This is a long line with <anchor name="inside"/> an anchor in the middle</paragraph></section>',
                '//a[@id="inside"]',
                function ( DOMNodeList $xpathResult ) use ( $that )
                {
                    $that->assertEquals( $xpathResult->length, 1 );
                    $doc = $xpathResult->item( 0 )->ownerDocument;
                    $that->assertEquals(
                        trim( $doc->saveXML( $doc->documentElement ) ),
                        '<p>This is a long line with <a id="inside"/> an anchor in the middle</p>'
                    );
                }
            )
        );
    }

    /**
     * @dataProvider dataProviderAnchor
     */
    public function testAnchorRendering( $xml, $xpathCheck, $checkClosure )
    {
        $dom = new DomDocument();
        $dom->loadXML( $xml );
        $html5 = new Html5( $this->getDefaultStylesheet(), array() );

        $result = new DomDocument();
        $result->loadXML( $html5->convert( $dom ) );
        $xpath = new DOMXPath( $result );
        $checkClosure( $xpath->query( $xpathCheck ) );
    }

    public function testAddPreConverter()
    {
        $html5Converter = new Html5( 'foo.xsl' );
        $converter1 = $this->getPreConvertMock();
        $html5Converter->addPreConverter( $converter1 );
        $converter2 = $this->getPreConvertMock();
        $html5Converter->addPreConverter( $converter2 );

        $this->assertSame( array( $converter1, $converter2 ), $html5Converter->getPreConverters() );
    }
}
