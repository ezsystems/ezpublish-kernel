<?php
/**
 * File containing the RichText Embed Render converter test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\RichText\Converter\Render;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\FieldType\RichText\Converter\Render\Embed;
use DOMDocument;

class EmbedTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rendererMock = $this->getRendererMock();
        $this->loggerMock = $this->getLoggerMock();
        parent::setUp();
    }

    public function providerForTestConvert()
    {
        return array(
            array(
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
                array(),
                array(
                    array(
                        "method" => "renderContentEmbed",
                        "id" => "106",
                        "view" => "embed",
                        "is_inline" => false,
                        "params" => array(
                            "id" => "106",
                            "view" => "embed",
                            "params" => array()
                        ),
                    )
                )
            ),
            array(
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
                array(),
                array(
                    array(
                        "method" => "renderLocationEmbed",
                        "id" => "601",
                        "view" => "embed-inline",
                        "is_inline" => false,
                        "params" => array(
                            "id" => "601",
                            "view" => "embed-inline",
                            "params" => array(
                                "size" => "medium",
                                "offset" => 10,
                                "limit" => 5,
                                "hey" => array(
                                    "look" => array(
                                        "at" => array(
                                            "this" => "wohoo",
                                            "that" => "weeee"
                                        )
                                    ),
                                    "what" => "get to the chopper"
                                )
                            )
                        ),
                    )
                )
            ),
            array(
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
                array(),
                array(
                    array(
                        "method" => "renderLocationEmbed",
                        "id" => "601",
                        "view" => "embed",
                        "is_inline" => false,
                        "params" => array(
                            "id" => "601",
                            "view" => "embed",
                            "params" => array()
                        ),
                    ),
                    array(
                        "method" => "renderContentEmbed",
                        "id" => "106",
                        "view" => "full",
                        "is_inline" => true,
                        "params" => array(
                            "id" => "106",
                            "view" => "full",
                            "params" => array()
                        ),
                    )
                )
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed view="embed"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed view="embed"/>
</section>',
                array(
                    "Could not embed resource: empty 'xlink:href' attribute"
                ),
                array()
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="eznodeassignment://106" view="embed"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns:xlink="http://www.w3.org/1999/xlink">
  <ezembed xlink:href="eznodeassignment://106" view="embed"/>
</section>',
                array(
                    "Could not embed resource: unhandled resource reference 'eznodeassignment://106'"
                ),
                array()
            ),
            array(
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
                array(),
                array(
                    array(
                        "method" => "renderContentEmbed",
                        "id" => "106",
                        "view" => "embed",
                        "is_inline" => false,
                        "params" => array(
                            "id" => "106",
                            "view" => "embed",
                            "params" => array()
                        ),
                    )
                )
            ),
            array(
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
                array(),
                array(
                    array(
                        "method" => "renderContentEmbed",
                        "id" => "106",
                        "view" => "embed-inline",
                        "is_inline" => true,
                        "params" => array(
                            "id" => "106",
                            "view" => "embed-inline",
                            "params" => array()
                        ),
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider providerForTestConvert
     */
    public function testConvert( $xmlString, $expectedXmlString, array $errors, array $renderParams )
    {
        if ( isset( $errors ) )
        {
            foreach ( $errors as $index => $error )
            {
                $this->loggerMock
                    ->expects( $this->at( $index ) )
                    ->method( "error" )
                    ->with( $error );
            }
        }
        else
        {
            $this->loggerMock->expects( $this->never() )->method( "error" );
        }

        $this->rendererMock->expects( $this->never() )->method( "renderTag" );

        if ( !empty( $renderParams ) )
        {
            foreach ( $renderParams as $index => $params )
            {
                $this->rendererMock
                    ->expects( $this->at( $index ) )
                    ->method( $params["method"] )
                    ->with(
                        $params["id"],
                        $params["view"],
                        $params["params"],
                        $params["is_inline"]
                    )
                    ->will( $this->returnValue( $params["id"] ) );
            }
        }
        else
        {
            $this->rendererMock->expects( $this->never() )->method( "renderContentEmbed" );
            $this->rendererMock->expects( $this->never() )->method( "renderLocationEmbed" );
        }

        $document = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $document->loadXML( $xmlString );

        $document = $this->getConverter()->convert( $document );

        $expectedDocument = new DOMDocument;
        $expectedDocument->preserveWhiteSpace = false;
        $expectedDocument->formatOutput = false;
        $expectedDocument->loadXML( $expectedXmlString );

        $this->assertEquals( $expectedDocument, $document );
    }

    protected function getConverter()
    {
        return new Embed(
            $this->rendererMock,
            $this->loggerMock
        );
    }

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRendererMock()
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\FieldType\\RichText\\RendererInterface"
        );
    }

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoggerMock()
    {
        return $this->getMock(
            "Psr\\Log\\LoggerInterface"
        );
    }
}
