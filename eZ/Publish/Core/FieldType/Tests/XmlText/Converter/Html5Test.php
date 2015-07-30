<?php

/**
 * File containing the Html5 converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\Expanding;
use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedLinking;
use eZ\Publish\Core\FieldType\XmlText\Converter\Html5;
use PHPUnit_Framework_TestCase;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Tests the Html5 converter.
 */
class Html5Test extends PHPUnit_Framework_TestCase
{
    protected $file;

    protected function getDefaultStylesheet()
    {
        if (!empty($this->file)) {
            return $this->file;
        }

        $file = __DIR__ . '/../../../XmlText/Input/Resources/stylesheets/eZXml2Html5_core.xsl';
        if (!file_exists($file)) {
            throw new \InvalidArgumentException('Could not find: ' . $file);
        }

        return $this->file = $file;
    }

    protected function getPreConvertMock()
    {
        return $this->getMockBuilder('eZ\Publish\Core\FieldType\XmlText\Converter')
            ->getMock();
    }

    public function dataProviderConstructorException()
    {
        return array(
            array(
                array(1, 2),
                array(1, $this->getPreConvertMock()),
                array($this->getPreConvertMock(), 1),
            ),
        );
    }

    /**
     * @dataProvider dataProviderConstructorException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testConstructorException(array $preConverters)
    {
        new Html5('', array(), $preConverters);
    }

    public function testPreConverterCalled()
    {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" encoding="utf-8"?><section/>');
        $preConverterMock1 = $this->getPreConvertMock();
        $preConverterMock2 = $this->getPreConvertMock();

        $preConverterMock1->expects($this->once())
            ->method('convert')
            ->with($this->equalTo($dom));

        $preConverterMock2->expects($this->once())
            ->method('convert')
            ->with($this->equalTo($dom));

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                $preConverterMock1,
                $preConverterMock2,
            )
        );
        $html5->convert($dom);
    }

    public function dataProviderAnchor()
    {
        $that = $this;

        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph><anchor name="start"/>This is the start</paragraph></section>',
                '//a[@id="start"]',
                function (DOMNodeList $xpathResult) use ($that) {
                    $that->assertEquals($xpathResult->length, 1);
                    $anchor = $xpathResult->item(0);
                    $that->assertEquals($anchor->parentNode->localName, 'p');
                },
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/">
    <paragraph><anchor name="start"/>This is<anchor name="middle"/> the start<anchor name="end"/></paragraph>
</section>',
                '//a[@id]',
                function (DOMNodeList $xpathResult) use ($that) {
                    $ids = array('start', 'middle', 'end');
                    $that->assertEquals($xpathResult->length, count($ids));
                    foreach ($xpathResult as $k => $anchor) {
                        $that->assertEquals(
                            $anchor->getAttribute('id'),
                            $ids[$k]
                        );
                        $that->assertEquals($anchor->parentNode->localName, 'p');
                    }
                },
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph>This is a long line with <anchor name="inside"/> an anchor in the middle</paragraph></section>',
                '//a[@id="inside"]',
                function (DOMNodeList $xpathResult) use ($that) {
                    $that->assertEquals($xpathResult->length, 1);
                    $doc = $xpathResult->item(0)->ownerDocument;
                    $that->assertEquals(
                        trim($doc->saveXML($doc->documentElement)),
                        '<p>This is a long line with <a id="inside"/> an anchor in the middle</p>'
                    );
                },
            ),
        );
    }

    /**
     * @dataProvider dataProviderAnchor
     */
    public function testAnchorRendering($xml, $xpathCheck, $checkClosure)
    {
        $dom = new DomDocument();
        $dom->loadXML($xml);
        $html5 = new Html5($this->getDefaultStylesheet(), array());

        $result = new DomDocument();
        $result->loadXML($html5->convert($dom));
        $xpath = new DOMXPath($result);
        $checkClosure($xpath->query($xpathCheck));
    }

    public function dataProviderLiteral()
    {
        $that = $this;

        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal>This is a &lt;em&gt;emphasized&lt;/em&gt; text</literal></paragraph></section>',
                '//pre',
                function (DOMNodeList $xpathResult) use ($that) {
                    $that->assertEquals($xpathResult->length, 1);
                    $doc = $xpathResult->item(0)->ownerDocument;
                    $that->assertEquals(
                        '<pre>This is a &lt;em&gt;emphasized&lt;/em&gt; text</pre>',
                        trim($doc->saveXML($doc->documentElement))
                    );
                },
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">&lt;iframe src="http://www.ez.no" width="500"/&gt;</literal></paragraph></section>',
                '//iframe',
                function (DOMNodeList $xpathResult) use ($that) {
                    $that->assertEquals($xpathResult->length, 1);
                    $doc = $xpathResult->item(0)->ownerDocument;
                    $that->assertEquals(
                        '<iframe src="http://www.ez.no" width="500"/>',
                        $doc->saveXML($doc->documentElement)
                    );
                },
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">&lt;div class="dummy"&gt;&lt;p&gt;First paragraph&lt;/p&gt;&lt;p&gt;Second paragraph with &lt;strong&gt;strong&lt;/strong&gt;&lt;/p&gt;&lt;/div&gt;</literal></paragraph></section>',
                '//div',
                function (DOMNodeList $xpathResult) use ($that) {
                    $that->assertEquals($xpathResult->length, 1);
                    $doc = $xpathResult->item(0)->ownerDocument;
                    $that->assertEquals(
                        '<div class="dummy"><p>First paragraph</p><p>Second paragraph with <strong>strong</strong></p></div>',
                        $doc->saveXML($doc->documentElement)
                    );
                },
            ),
        );
    }

    /**
     * @dataProvider dataProviderLiteral
     */
    public function testLiteralRendering($xml, $xpathCheck, $checkClosure)
    {
        $dom = new DomDocument();
        $dom->loadXML($xml);
        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );

        $result = new DomDocument();
        $result->loadXML($html5->convert($dom));
        $xpath = new DOMXPath($result);
        $checkClosure($xpath->query($xpathCheck));
    }

    public function testConvertReturnsNotValidXml()
    {
        $dom = new DomDocument();
        $dom->loadXML(
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">This is only a literal with &lt;strong&gt;strong&lt;/strong&gt; text</literal></paragraph></section>'
        );
        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );
        $result = $html5->convert($dom);

        $this->assertEquals(
            $result,
            'This is only a literal with <strong>strong</strong> text
'
        );

        $dom->loadXML(
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><literal class="html">This is text followed by an iframe &lt;iframe src="http://www.ez.no" /&gt;</literal></paragraph></section>'
        );
        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );
        $result = $html5->convert($dom);

        $this->assertEquals(
            $result,
            'This is text followed by an iframe <iframe src="http://www.ez.no" />
'
        );
    }

    public function testAddPreConverter()
    {
        $html5Converter = new Html5('foo.xsl');
        $converter1 = $this->getPreConvertMock();
        $html5Converter->addPreConverter($converter1);
        $converter2 = $this->getPreConvertMock();
        $html5Converter->addPreConverter($converter2);

        $this->assertSame(array($converter1, $converter2), $html5Converter->getPreConverters());
    }

    public function testTableRendering()
    {
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML(
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/">
    <section>
        <header>Heading 2</header>
        <header>Heading 2</header>
        <section>
            <header>Heading 3</header>
            <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
                <table border="1" width="100%" class="class1" custom:summary="summary1">
                    <tr>
                        <td>
                            <section>
                                <header>Heading 2</header>
                                <header>Heading 2</header>
                                <section>
                                    <header>Heading 3</header>
                                    <header>Heading 3</header>
                                </section>
                            </section>
                            <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
                                <table border="1" width="100%" class="class2" custom:summary="summary2">
                                    <tr>
                                        <td>
                                            <section>
                                                <header>Heading 2</header>
                                                <section>
                                                    <header>Heading 3</header>
                                                </section>
                                            </section>
                                        </td>
                                    </tr>
                                </table>
                            </paragraph>
                        </td>
                    </tr>
                </table>
            </paragraph>
        </section>
    </section>
</section>'
        );

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );
        $result = $html5->convert($xmlDoc);

        // Make <br> tags valid
        $result = str_replace('<br>', '<br/>', $result);
        // Make a valid XML document string
        $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><section xmlns=\"http://ez.no/namespaces/ezpublish5/xhtml5\">{$result}</section>";

        $convertedDocument = $this->createDocument($result, false);

        $aux = '<a id="eztoc_1_1"></a><h2>Heading 2</h2>
<a id="eztoc_1_2"></a><h2>Heading 2</h2>
<a id="eztoc_1_3_1"></a><h3>Heading 3</h3>
<table class="class1" border="1" cellpadding="2" cellspacing="0" width="100%" style="width:100%;" summary="summary1">
<tr>
<td valign="top" style="vertical-align: top;">
<a id="eztoc_1_3_1_1"></a><h2>Heading 2</h2>
<a id="eztoc_1_3_1_2"></a><h2>Heading 2</h2>
<a id="eztoc_1_3_1_3_1"></a><h3>Heading 3</h3>
<a id="eztoc_1_3_1_3_2"></a><h3>Heading 3</h3>
<table class="class2" border="1" cellpadding="2" cellspacing="0" width="100%" style="width:100%;" summary="summary2">
<tr>
<td valign="top" style="vertical-align: top;">
<a id="eztoc_1_3_1_1"></a><h2>Heading 2</h2>
<a id="eztoc_1_3_1_2_1"></a><h3>Heading 3</h3>
</td>
</tr>
</table>
</td>
</tr>
</table>
';
        $aux = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><section xmlns=\"http://ez.no/namespaces/ezpublish5/xhtml5\">{$aux}</section>";

        $expectedDocument = $this->createDocument($aux, false);

        $this->assertEquals(
            $expectedDocument,
            $convertedDocument
        );
    }

    public function dataProviderEmbedRendering()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph><embed view="embed" size="small" object_id="42"/></paragraph></section>',
                '<div></div>',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph class="test"><embed view="embed" size="small" align="left" object_id="42"/>First paragraph</paragraph><paragraph>Second paragraph</paragraph></section>',
                '<div class="object-left"></div><p class="test">First paragraph</p><p>Second paragraph</p>',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph class="test"><embed view="embed" size="small" align="left" object_id="42"/>First<anchor name="middle"/>paragraph</paragraph><paragraph>Second paragraph</paragraph></section>',
                '<div class="object-left"></div><p class="test">First<a id="middle"></a>paragraph</p><p>Second paragraph</p>',
            ),
        );
    }

    /**
     * @dataProvider dataProviderEmbedRendering
     */
    public function testEmbedRendering($xml, $expected)
    {
        $dom = new DomDocument();
        $dom->loadXML($xml);

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );
        $result = $html5->convert($dom);

        $this->assertEquals(
            $expected,
            trim($result)
        );
    }

    /**
     * Provider for conversion test.
     *
     * @return array
     */
    public function providerForTestConvert()
    {
        $map = array();

        foreach (glob(__DIR__ . '/_fixtures/html5/input/*.xml') as $inputFilePath) {
            $basename = basename($inputFilePath, '.xml');
            $outputFilePath = __DIR__ . "/_fixtures/html5/output/{$basename}.xml";

            $map[] = array($inputFilePath, $outputFilePath);
        }

        return $map;
    }

    /**
     * @param string $xml
     * @param bool $isPath
     *
     * @return \DOMDocument
     */
    protected function createDocument($xml, $isPath = true)
    {
        $document = new DOMDocument();

        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;

        if ($isPath === true) {
            $xml = file_get_contents($xml);
        }

        $document->loadXml($xml);

        return $document;
    }

    /**
     * @param string $inputFilePath
     * @param string $outputFilePath
     *
     * @dataProvider providerForTestConvert
     */
    public function testConvert($inputFilePath, $outputFilePath)
    {
        $inputDocument = $this->createDocument($inputFilePath);

        $html5 = new Html5(
            $this->getDefaultStylesheet(),
            array(),
            array(
                new Expanding(),
                new EmbedLinking(),
            )
        );

        $result = $html5->convert($inputDocument);
        // Make <br> tags valid
        $result = str_replace('<br>', '<br/>', $result);
        // Make a valid XML document string
        $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><section xmlns=\"http://ez.no/namespaces/ezpublish5/xhtml5\">{$result}</section>";

        $convertedDocument = $this->createDocument($result, false);
        $expectedDocument = $this->createDocument($outputFilePath);

        $this->assertEquals(
            $expectedDocument,
            $convertedDocument
        );
    }
}
