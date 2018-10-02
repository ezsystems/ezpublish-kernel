<?php

/**
 * File containing the RichText Template Render converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Render;

use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render\Template;
use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use DOMDocument;

class TemplateTest extends TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rendererMock;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $converterMock;

    public function setUp()
    {
        $this->rendererMock = $this->getRendererMock();
        $this->converterMock = $this->getConverterMock();
        parent::setUp();
    }

    public function providerForTestConvert()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template1"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template1">
    <ezpayload><![CDATA[template1]]></ezpayload>
  </eztemplate>
</section>',
                array(
                    array(
                        'name' => 'template1',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template1',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template2">
    <ezcontent>content2</ezcontent>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
      <ezvalue key="offset">10</ezvalue>
      <ezvalue key="limit">5</ezvalue>
      <ezvalue key="hey">
          <ezvalue key="look">
              <ezvalue key="at">
                  <ezvalue key="this">wohoo</ezvalue>
                  <ezvalue key="that">weeee</ezvalue>
              </ezvalue>
          </ezvalue>
          <ezvalue key="what">get to the chopper</ezvalue>
      </ezvalue>
    </ezconfig>
  </eztemplate>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template2">
    <ezcontent>content2</ezcontent>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
      <ezvalue key="offset">10</ezvalue>
      <ezvalue key="limit">5</ezvalue>
      <ezvalue key="hey">
          <ezvalue key="look">
              <ezvalue key="at">
                  <ezvalue key="this">wohoo</ezvalue>
                  <ezvalue key="that">weeee</ezvalue>
              </ezvalue>
          </ezvalue>
          <ezvalue key="what">get to the chopper</ezvalue>
      </ezvalue>
    </ezconfig>
    <ezpayload><![CDATA[template2]]></ezpayload>
  </eztemplate>
</section>',
                array(
                    array(
                        'name' => 'template2',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template2',
                            'content' => 'content2',
                            'params' => array(
                                'size' => 'medium',
                                'offset' => 10,
                                'limit' => 5,
                                'hey' => array(
                                    'look' => array(
                                        'at' => array(
                                            'this' => 'wohoo',
                                            'that' => 'weeee',
                                        ),
                                    ),
                                    'what' => 'get to the chopper',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template3"/>
  <eztemplateinline name="template4"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template3">
    <ezpayload><![CDATA[template3]]></ezpayload>
  </eztemplate>
  <eztemplateinline name="template4">
    <ezpayload><![CDATA[template4]]></ezpayload>
  </eztemplateinline>
</section>',
                array(
                    array(
                        'name' => 'template3',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template3',
                            'params' => array(),
                        ),
                    ),
                    array(
                        'name' => 'template4',
                        'is_inline' => true,
                        'params' => array(
                            'name' => 'template4',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template5"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template5">
    <ezpayload><![CDATA[template5]]></ezpayload>
  </eztemplate>
</section>',
                array(
                    array(
                        'name' => 'template5',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template5',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplateinline name="template6"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplateinline name="template6">
    <ezpayload><![CDATA[template6]]></ezpayload>
  </eztemplateinline>
</section>',
                array(
                    array(
                        'name' => 'template6',
                        'is_inline' => true,
                        'params' => array(
                            'name' => 'template6',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template7">
    <ezcontent>content7<eztemplate name="template8">
    <ezcontent>content8</ezcontent>
  </eztemplate></ezcontent>
  </eztemplate>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template7">
    <ezcontent>content7template8</ezcontent>
    <ezpayload><![CDATA[template7]]></ezpayload>
  </eztemplate>
</section>',
                array(
                    array(
                        'name' => 'template7',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template7',
                            'content' => 'content7<eztemplate name="template8"><ezcontent>content8</ezcontent></eztemplate>',
                            'params' => array(),
                        ),
                    ),
                    array(
                        'name' => 'template8',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template8',
                            'content' => 'content8',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <eztemplate name="custom_tag" ezxhtml:align="right">
    <ezcontent><para>Param: value</para></ezcontent>
    <ezconfig>
        <ezvalue key="param">value</ezvalue>
    </ezconfig>
  </eztemplate>
</section>',
                '<?xml version="1.0"?>
 <section xmlns="http://docbook.org/ns/docbook" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <eztemplate name="custom_tag" ezxhtml:align="right">
    <ezcontent>
      <para>Param: value</para>
    </ezcontent>
    <ezconfig>
      <ezvalue key="param">value</ezvalue>
    </ezconfig>
    <ezpayload>custom_tag</ezpayload>
  </eztemplate>
</section>',
                array(
                    array(
                        'name' => 'custom_tag',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'custom_tag',
                            'content' => '<para>Param: value</para>',
                            'params' => array('param' => 'value'),
                            'align' => 'right',
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="template9" type="tag"/>
  <eztemplateinline name="template10" type="tag"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="template9" type="tag">
    <ezpayload><![CDATA[template9]]></ezpayload>
  </eztemplate>
  <eztemplateinline name="template10" type="tag">
    <ezpayload><![CDATA[template10]]></ezpayload>
  </eztemplateinline>
</section>',
                array(
                    array(
                        'name' => 'template9',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'template9',
                            'params' => array(),
                        ),
                    ),
                    array(
                        'name' => 'template10',
                        'is_inline' => true,
                        'params' => array(
                            'name' => 'template10',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="style1" type="style"><ezcontent>style 1 content</ezcontent></eztemplate>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="style1" type="style"><ezcontent>style 1 content</ezcontent><ezpayload><![CDATA[style1]]></ezpayload></eztemplate>
</section>',
                array(
                    array(
                        'name' => 'style1',
                        'type' => 'style',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'style1',
                            'content' => 'style 1 content',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="style2" type="style"><ezcontent>style 2 content</ezcontent></eztemplate>
  <eztemplateinline name="style3" type="style"><ezcontent>style 3 content</ezcontent></eztemplateinline>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom">
  <eztemplate name="style2" type="style"><ezcontent>style 2 content</ezcontent><ezpayload><![CDATA[style2]]></ezpayload></eztemplate>
  <eztemplateinline name="style3" type="style"><ezcontent>style 3 content</ezcontent><ezpayload><![CDATA[style3]]></ezpayload></eztemplateinline>
</section>',
                array(
                    array(
                        'name' => 'style2',
                        'type' => 'style',
                        'is_inline' => false,
                        'params' => array(
                            'name' => 'style2',
                            'content' => 'style 2 content',
                            'params' => array(),
                        ),
                    ),
                    array(
                        'name' => 'style3',
                        'type' => 'style',
                        'is_inline' => true,
                        'params' => array(
                            'name' => 'style3',
                            'content' => 'style 3 content',
                            'params' => array(),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider providerForTestConvert
     */
    public function testConvert($xmlString, $expectedXmlString, array $renderParams)
    {
        $this->rendererMock->expects($this->never())->method('renderContentEmbed');
        $this->rendererMock->expects($this->never())->method('renderLocationEmbed');

        if (!empty($renderParams)) {
            $convertIndex = 0;
            foreach ($renderParams as $index => $params) {
                if (!empty($params['type']) && $params['type'] === 'style') {
                    // mock simple converter
                    $contentDoc = new DOMDocument();
                    $contentDoc->appendChild($contentDoc->createTextNode($params['params']['content']));
                    $this->converterMock
                        ->expects($this->at($convertIndex++))
                        ->method('convert')
                        ->with($contentDoc)
                        ->will($this->returnValue($contentDoc));
                }
                $this->rendererMock
                    ->expects($this->at($index))
                    ->method('renderTemplate')
                    ->with(
                        $params['name'],
                        isset($params['type']) ? $params['type'] : 'tag',
                        $params['params'],
                        $params['is_inline']
                    )
                    ->will($this->returnValue($params['name']));
            }
        } else {
            $this->rendererMock->expects($this->never())->method('renderTemplate');
        }

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $document->loadXML($xmlString);

        $document = $this->getConverter()->convert($document);

        $expectedDocument = new DOMDocument();
        $expectedDocument->preserveWhiteSpace = false;
        $expectedDocument->formatOutput = false;
        $expectedDocument->loadXML($expectedXmlString);

        $this->assertEquals($expectedDocument, $document);
    }

    protected function getConverter()
    {
        return new Template($this->rendererMock, $this->converterMock);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRendererMock()
    {
        return $this->createMock(RendererInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConverterMock()
    {
        return $this->createMock(Converter::class);
    }
}
