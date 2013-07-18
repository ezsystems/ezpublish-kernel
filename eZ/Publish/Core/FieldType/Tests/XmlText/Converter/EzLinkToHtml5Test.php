<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UrlAlias;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException as APIUnauthorizedException;

/**
 * Tests the EzLinkToHtml5 Preconverter
 * Class EmbedToHtml5Test
 * @package eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter
 */
class EzLinkToHtml5Test extends PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockContentService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\ContentService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLocationService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\LocationService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUrlAliasService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\URLAliasService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $contentService
     * @param $locationService
     * @param $urlAliasService
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\Repository
     */
    protected function getMockRepository( $contentService, $locationService, $urlAliasService )
    {
        $repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );

        $repository->expects( $this->any() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $contentService ) );

        $repository->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationService ) );

        $repository->expects( $this->any() )
            ->method( 'getURLAliasService' )
            ->will( $this->returnValue( $urlAliasService ) );

        return $repository;
    }

    /**
     * @return array
     */
    public function providerLinkXmlSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="/test">Link text</link>
  </para>
</article>',
                '/test',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="/test#anchor">Link text</link>
  </para>
</article>',
                '/test#anchor',
            ),
        );
    }

    /**
     * Test conversion of ezurl://<id> links
     *
     * @dataProvider providerLinkXmlSample
     */
    public function testLink( $xmlString, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasService = $this->getMockUrlAliasService();

        $contentService->expects( $this->never() )
            ->method( $this->anything() );

        $locationService->expects( $this->never() )
            ->method( $this->anything() );

        $urlAliasService->expects( $this->never() )
            ->method( $this->anything() );

        $repository = $this->getMockRepository(
            $contentService,
            $locationService,
            $urlAliasService
        );

        $converter = new EzLinkToHtml5( $repository );

        $xmlDoc = $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );

        $this->assertEquals( 1, $links->length );
        $this->assertEquals( $url, $links->item( 0 )->getAttribute( 'xlink:href' ) );
    }

    /**
     * @return array
     */
    public function providerLocationLink()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezlocation://106">Content name</link>
  </para>
</article>',
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezlocation://106#anchor">Content name</link>
  </para>
</article>',
                106,
                'test',
                'test#anchor',
            ),
        );
    }

    /**
     * Test conversion of ezlocation://<id> links
     *
     * @dataProvider providerLocationLink
     */
    public function testConvertLocationLink( $xmlString, $locationId, $rawUrl, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasService = $this->getMockUrlAliasService();

        $location = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' );
        $urlAlias = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\URLAlias' );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $urlAliasService->expects( $this->once() )
            ->method( 'reverseLookup' )
            ->with( $this->equalTo( $location ) )
            ->will( $this->returnValue( $urlAlias ) );

        $urlAlias->expects( $this->once() )
            ->method( '__get' )
            ->with( $this->equalTo( 'path' ) )
            ->will( $this->returnValue( $rawUrl ) );

        $repository = $this->getMockRepository(
            $contentService,
            $locationService,
            $urlAliasService
        );

        $converter = new EzLinkToHtml5( $repository );

        $xmlDoc = $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );

        $this->assertEquals( 1, $links->length );
        $this->assertEquals( $url, $links->item( 0 )->getAttribute( 'xlink:href' ) );
    }

    /**
     * @return array
     */
    public function providerBadLocationLink()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezlocation://106">Content name</link>
  </para>
</article>',
                106,
                new APINotFoundException( "Location", 106 ),
                'warning',
                'While generating links for xmltext, could not locate Location with ID 106'
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezlocation://106">Content name</link>
  </para>
</article>',
                106,
                new APIUnauthorizedException( "Location", 106 ),
                'notice',
                'While generating links for xmltext, unauthorized to load Location with ID 106'
            )
        );
    }

    /**
     * Test logging of bad location links
     *
     * @dataProvider providerBadLocationLink
     */
    public function testConvertBadLocationLink( $xmlString, $locationId, $exception, $logType, $logMessage )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasService = $this->getMockUrlAliasService();

        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );

        $logger->expects( $this->once() )
            ->method( $logType )
            ->with( $this->equalTo( $logMessage ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->throwException( $exception ) );

        $repository = $this->getMockRepository(
            $contentService,
            $locationService,
            $urlAliasService
        );

        $converter = new EzLinkToHtml5( $repository, $logger );

        $converter->convert( $xmlDoc );
    }

    /**
     * @return array
     */
    public function providerContentLink()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezcontent://104">Content name</link>
  </para>
</article>',
                104,
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezcontent://104#anchor">Content name</link>
  </para>
</article>',
                104,
                106,
                'test',
                'test#anchor',
            ),
        );
    }

    /**
     * Test conversion of ezcontent://<id> links
     *
     * @dataProvider providerContentLink
     */
    public function testConvertContentLink( $xmlString, $contentId, $locationId, $rawUrl, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasService = $this->getMockUrlAliasService();

        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $contentInfo = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' );
        $location = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' );
        $urlAlias = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\URLAlias' );

        $content->expects( $this->once() )
            ->method( '__get' )
            ->with( $this->equalTo( 'contentInfo' ) )
            ->will( $this->returnValue( $contentInfo ) );

        $contentInfo->expects( $this->once() )
            ->method( '__get' )
            ->with( $this->equalTo( 'mainLocationId' ) )
            ->will( $this->returnValue( $locationId ) );

        $contentService->expects( $this->any() )
            ->method( 'loadContent' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $content ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $urlAliasService->expects( $this->once() )
            ->method( 'reverseLookup' )
            ->with( $this->equalTo( $location ) )
            ->will( $this->returnValue( $urlAlias ) );

        $urlAlias->expects( $this->once() )
            ->method( '__get' )
            ->with( $this->equalTo( 'path' ) )
            ->will( $this->returnValue( $rawUrl ) );

        $repository = $this->getMockRepository(
            $contentService,
            $locationService,
            $urlAliasService
        );

        $converter = new EzLinkToHtml5( $repository );

        $xmlDoc = $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );

        $this->assertEquals( 1, $links->length );
        $this->assertEquals( $url, $links->item( 0 )->getAttribute( 'xlink:href' ) );
    }

    /**
     * @return array
     */
    public function providerBadContentLink()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezcontent://205">Content name</link>
  </para>
</article>',
                205,
                new APINotFoundException( "Content", 205 ),
                'warning',
                'While generating links for xmltext, could not locate Content object with ID 205'
            ),
            array(
                '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>Link example</title>
  <para>
    <link xlink:href="ezcontent://205">Content name</link>
  </para>
</article>',
                205,
                new APIUnauthorizedException( "Content", 205 ),
                'notice',
                'While generating links for xmltext, unauthorized to load Content object with ID 205'
            )
        );
    }

    /**
     * Test logging of bad content links
     *
     * @dataProvider providerBadContentLink
     */
    public function testConvertBadContentLink( $xmlString, $contentId, $exception, $logType, $logMessage )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasService = $this->getMockUrlAliasService();

        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );

        $logger->expects( $this->once() )
            ->method( $logType )
            ->with( $this->equalTo( $logMessage ) );

        $contentService->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->throwException( $exception ) );

        $repository = $this->getMockRepository(
            $contentService,
            $locationService,
            $urlAliasService
        );

        $converter = new EzLinkToHtml5( $repository, $logger );

        $converter->convert( $xmlDoc );
    }
}
