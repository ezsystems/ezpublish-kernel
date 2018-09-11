<?php

/**
 * File containing the RichText Template Render converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license   For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Render;

use DOMDocument;
use eZ\Publish\Core\FieldType\RichText\Converter;
use eZ\Publish\Core\FieldType\RichText\Converter\Render\Style;
use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for RichText Custom Styles Renderer.
 */
class StyleTest extends TestCase
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
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <ezstyle name="style1">style 1 content</ezstyle>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <ezstyle name="style1">style 1 content<ezpayload><![CDATA[style1]]></ezpayload></ezstyle>
</section>',
                [
                    [
                        'name' => 'style1',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'style1',
                            'content' => 'style 1 content',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <ezstyle name="style2">style 2 content</ezstyle>
  <ezstyleinline name="style3">style 3 content</ezstyleinline>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <ezstyle name="style2">style 2 content<ezpayload><![CDATA[style2]]></ezpayload></ezstyle>
  <ezstyleinline name="style3">style 3 content<ezpayload><![CDATA[style3]]></ezpayload></ezstyleinline>
</section>',
                [
                    [
                        'name' => 'style2',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'style2',
                            'content' => 'style 2 content',
                        ],
                    ],
                    [
                        'name' => 'style3',
                        'is_inline' => true,
                        'params' => [
                            'name' => 'style3',
                            'content' => 'style 3 content',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestConvert
     */
    public function testConvert($xmlString, $expectedXmlString, array $renderParams)
    {
        $this->rendererMock->expects($this->never())->method('renderContentEmbed');
        $this->rendererMock->expects($this->never())->method('renderLocationEmbed');
        $this->rendererMock->expects($this->never())->method('renderTag');

        if (!empty($renderParams)) {
            foreach ($renderParams as $index => $params) {
                $this->rendererMock
                    ->expects($this->at($index))
                    ->method('renderStyle')
                    ->with(
                        $params['name'],
                        $params['params'],
                        $params['is_inline']
                    )
                    ->will($this->returnValue($params['name']));
            }
        } else {
            $this->rendererMock->expects($this->never())->method('renderStyle');
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
        return new Style($this->rendererMock, $this->converterMock);
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
