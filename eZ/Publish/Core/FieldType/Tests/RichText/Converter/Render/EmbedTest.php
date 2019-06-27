<?php

/**
 * File containing the RichText Embed Render converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Render;

use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\FieldType\RichText\Converter\Render\Embed;
use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use DOMDocument;
use Psr\Log\LoggerInterface;

class EmbedTest extends TestCase
{
    public function setUp()
    {
        $this->rendererMock = $this->getRendererMock();
        $this->loggerMock = $this->getLoggerMock();
        parent::setUp();
    }

    public function providerForTestConvert()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106" view="embed"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106" view="embed">
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembed>
</section>',
                [],
                [
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'embed',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'embed',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembed xlink:href="ezcontent://106" view="embed" xml:id="embed-id-1" ezxhtml:class="embed-class" ezxhtml:align="left">
    <ezlink href_resolved="RESOLVED" xlink:href="ezurl://95#fragment1" xlink:show="new" xml:id="link-id-1" xlink:title="Link title" ezxhtml:class="link-class"/>
  </ezembed>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembed xlink:href="ezcontent://106" view="embed" xml:id="embed-id-1" ezxhtml:class="embed-class" ezxhtml:align="left">
    <ezlink href_resolved="RESOLVED" xlink:href="ezurl://95#fragment1" xlink:show="new" xml:id="link-id-1" xlink:title="Link title" ezxhtml:class="link-class"/>
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembed>
</section>',
                [],
                [
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'embed',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'embed',
                            'link' => [
                                'href' => 'RESOLVED',
                                'resourceType' => 'URL',
                                'resourceId' => null,
                                'wrapped' => false,
                                'target' => '_blank',
                                'title' => 'Link title',
                                'id' => 'link-id-1',
                                'class' => 'link-class',
                            ],
                            'class' => 'embed-class',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezlocation://601" view="embed-inline">
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
  </ezembed>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezlocation://601" view="embed-inline">
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
    <ezpayload><![CDATA[601]]></ezpayload>
  </ezembed>
</section>',
                [],
                [
                    [
                        'method' => 'renderLocationEmbed',
                        'id' => '601',
                        'viewType' => 'embed-inline',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '601',
                            'viewType' => 'embed-inline',
                            'config' => [
                                'size' => 'medium',
                                'offset' => 10,
                                'limit' => 5,
                                'hey' => [
                                    'look' => [
                                        'at' => [
                                            'this' => 'wohoo',
                                            'that' => 'weeee',
                                        ],
                                    ],
                                    'what' => 'get to the chopper',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembed xlink:href="ezlocation://601" view="embed-inline">
    <ezlink href_resolved="RESOLVED" xlink:href="ezcontent://95#fragment1" xlink:show="replace"/>
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
  </ezembed>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembed xlink:href="ezlocation://601" view="embed-inline">
    <ezlink href_resolved="RESOLVED" xlink:href="ezcontent://95#fragment1" xlink:show="replace"/>
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
    <ezpayload><![CDATA[601]]></ezpayload>
  </ezembed>
</section>',
                [],
                [
                    [
                        'method' => 'renderLocationEmbed',
                        'id' => '601',
                        'viewType' => 'embed-inline',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '601',
                            'viewType' => 'embed-inline',
                            'config' => [
                                'size' => 'medium',
                                'offset' => 10,
                                'limit' => 5,
                                'hey' => [
                                    'look' => [
                                        'at' => [
                                            'this' => 'wohoo',
                                            'that' => 'weeee',
                                        ],
                                    ],
                                    'what' => 'get to the chopper',
                                ],
                            ],
                            'link' => [
                                'href' => 'RESOLVED',
                                'resourceType' => 'CONTENT',
                                'resourceId' => '95',
                                'resourceFragmentIdentifier' => 'fragment1',
                                'wrapped' => false,
                                'target' => '_self',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezlocation://601" view="embed"/>
  <ezembedinline xlink:href="ezcontent://106" view="full"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezlocation://601" view="embed">
    <ezpayload><![CDATA[601]]></ezpayload>
  </ezembed>
  <ezembedinline xlink:href="ezcontent://106" view="full">
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembedinline>
</section>',
                [],
                [
                    [
                        'method' => 'renderLocationEmbed',
                        'id' => '601',
                        'viewType' => 'embed',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '601',
                            'viewType' => 'embed',
                        ],
                    ],
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'full',
                        'is_inline' => true,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'full',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembedinline xlink:href="ezlocation://601" view="embed">
    <ezlink href_resolved="RESOLVED" xlink:href="ezcontent://95"/>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
    </ezconfig>
  </ezembedinline>
  <paragraph>Here is one <link>linked <ezembedinline xlink:href="ezcontent://106" view="full">
    <ezlink href_resolved="RESOLVED2" xlink:href="ezlocation://59#fragment"/>
    <ezconfig>
      <ezvalue key="size">small</ezvalue>
    </ezconfig>
  </ezembedinline> inline</link> embed</paragraph>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <ezembedinline xlink:href="ezlocation://601" view="embed">
    <ezlink href_resolved="RESOLVED" xlink:href="ezcontent://95"/>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
    </ezconfig>
    <ezpayload><![CDATA[601]]></ezpayload>
  </ezembedinline>
  <paragraph>Here is one <link>linked <ezembedinline xlink:href="ezcontent://106" view="full">
    <ezlink href_resolved="RESOLVED2" xlink:href="ezlocation://59#fragment"/>
    <ezconfig>
      <ezvalue key="size">small</ezvalue>
    </ezconfig>
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembedinline> inline</link> embed</paragraph>
</section>',
                [],
                [
                    [
                        'method' => 'renderLocationEmbed',
                        'id' => '601',
                        'viewType' => 'embed',
                        'is_inline' => true,
                        'embedParams' => [
                            'id' => '601',
                            'viewType' => 'embed',
                            'config' => [
                                'size' => 'medium',
                            ],
                            'link' => [
                                'href' => 'RESOLVED',
                                'resourceType' => 'CONTENT',
                                'resourceId' => '95',
                                'wrapped' => false,
                            ],
                        ],
                    ],
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'full',
                        'is_inline' => true,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'full',
                            'config' => [
                                'size' => 'small',
                            ],
                            'link' => [
                                'href' => 'RESOLVED2',
                                'resourceType' => 'LOCATION',
                                'resourceId' => '59',
                                'resourceFragmentIdentifier' => 'fragment',
                                'wrapped' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed view="embed"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed view="embed"/>
</section>',
                [
                    "Could not embed resource: empty 'xlink:href' attribute",
                ],
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="eznodeassignment://106" view="embed"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="eznodeassignment://106" view="embed"/>
</section>',
                [
                    "Could not embed resource: unhandled resource reference 'eznodeassignment://106'",
                ],
                [],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106" view="embed">
    <ezlink xlink:href="ezcontent://601"/>
  </ezembed>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106" view="embed">
    <ezlink xlink:href="ezcontent://601"/>
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembed>
</section>',
                [
                    'Could not create link parameters: resolved embed link is missing',
                ],
                [
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'embed',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'embed',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="ezcontent://106">
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembed>
</section>',
                [],
                [
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'embed',
                        'is_inline' => false,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'embed',
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembedinline xlink:href="ezcontent://106"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembedinline xlink:href="ezcontent://106">
    <ezpayload><![CDATA[106]]></ezpayload>
  </ezembedinline>
</section>',
                [],
                [
                    [
                        'method' => 'renderContentEmbed',
                        'id' => '106',
                        'viewType' => 'embed-inline',
                        'is_inline' => true,
                        'embedParams' => [
                            'id' => '106',
                            'viewType' => 'embed-inline',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestConvert
     */
    public function testConvert($xmlString, $expectedXmlString, array $errors, array $renderParams)
    {
        if (isset($errors)) {
            foreach ($errors as $index => $error) {
                $this->loggerMock
                    ->expects($this->at($index))
                    ->method('error')
                    ->with($error);
            }
        } else {
            $this->loggerMock->expects($this->never())->method('error');
        }

        $this->rendererMock->expects($this->never())->method('renderTemplate');

        if (!empty($renderParams)) {
            foreach ($renderParams as $index => $params) {
                $this->rendererMock
                    ->expects($this->at($index))
                    ->method($params['method'])
                    ->with(
                        $params['id'],
                        $params['viewType'],
                        [
                            'embedParams' => $params['embedParams'],
                        ],
                        $params['is_inline']
                    )
                    ->will($this->returnValue($params['id']));
            }
        } else {
            $this->rendererMock->expects($this->never())->method('renderContentEmbed');
            $this->rendererMock->expects($this->never())->method('renderLocationEmbed');
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
        return new Embed(
            $this->rendererMock,
            $this->loggerMock
        );
    }

    /** @var \eZ\Publish\Core\FieldType\RichText\RendererInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $rendererMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRendererMock()
    {
        return $this->createMock(RendererInterface::class);
    }

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
