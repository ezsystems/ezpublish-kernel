<?php
/**
 * File containing the EzLinkToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException as APIUnauthorizedException;

/**
 * Tests the EzLinkToHtml5 Preconverter
 * Class EmbedToHtml5Test
 * @package eZ\Publish\Core\Repository\DomainLogic\Tests\FieldType\XmlText\Converter
 */
class EzLinkToHtml5Test extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function providerLinkXmlSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link url="/test">object link</link>.</paragraph></section>',
                '/test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link url="/test" anchor_name="anchor">object link</link>.</paragraph></section>',
                '/test#anchor',
            ),
        );
    }

    /**
     * @return array
     */
    public function providerObjectLinkXmlSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link object_id="104">object link</link>.</paragraph></section>',
                104,
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link object_id="104" anchor_name="anchor">object link</link>.</paragraph></section>',
                104,
                106,
                'test',
                'test#anchor',
            ),
        );
    }

    /**
     * @return array
     */
    public function providerLocationLinkXmlSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is a <link node_id="106">node link</link>.</paragraph></section>',
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is a <link node_id="106" anchor_name="anchor">node link</link>.</paragraph></section>',
                106,
                'test',
                'test#anchor',
            ),
        );
    }

    /**
     * @return array
     */
    public function providerBadLocationSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is a <link node_id="106">node link</link>.</paragraph></section>',
                106,
                new APINotFoundException( "Location", 106 ),
                'warning',
                'While generating links for xmltext, could not locate Location with ID 106'
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is a <link node_id="106">node link</link>.</paragraph></section>',
                106,
                new APIUnauthorizedException( "Location", 106 ),
                'notice',
                'While generating links for xmltext, unauthorized to load Location with ID 106'
            )
        );
    }

    /**
     * @return array
     */
    public function providerBadObjectSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link object_id="205">object link</link>.</paragraph></section>',
                205,
                new APINotFoundException( "Content", 205 ),
                'warning',
                'While generating links for xmltext, could not locate Content object with ID 205'
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link object_id="205">object link</link>.</paragraph></section>',
                205,
                new APIUnauthorizedException( "Content", 205 ),
                'notice',
                'While generating links for xmltext, unauthorized to load Content object with ID 205'
            )
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockContentService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\DomainLogic\ContentService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLocationService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\DomainLogic\LocationService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUrlAliasService()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\Repository\DomainLogic\URLAliasService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUrlAliasRouter()
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\Routing\\UrlAliasRouter' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $contentService
     * @param $locationService
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * Test setting of urls on links with node_id attributes
     * @dataProvider providerLinkXmlSample
     * @param $xmlString
     * @param $url
     */
    public function testLink( $xmlString, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $contentService->expects( $this->never() )
            ->method( $this->anything() );

        $locationService->expects( $this->never() )
            ->method( $this->anything() );

        $urlAliasRouter->expects( $this->never() )
            ->method( $this->anything() );

        $converter = new EzLinkToHtml5( $locationService, $contentService, $urlAliasRouter );
        $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );
        foreach ( $links as $link )
        {
            // assumes only one link, or all pointing to same url
            $this->assertEquals( $url, $link->getAttribute( 'url' ) );
        }
    }

    /**
     * Test setting of urls on links with node_id attributes
     * @dataProvider providerLocationLinkXmlSample
     * @param $xmlString
     * @param $locationId
     * @param $rawUrl
     * @param $url
     */
    public function testLocationLink( $xmlString, $locationId, $rawUrl, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $location = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $urlAliasRouter->expects( $this->once() )
            ->method( 'generate' )
            ->with( $this->equalTo( $location ) )
            ->will( $this->returnValue( $rawUrl ) );

        $converter = new EzLinkToHtml5( $locationService, $contentService, $urlAliasRouter );
        $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );

        foreach ( $links as $link )
        {
            if ( $link->getAttribute( 'node_id' ) == $locationId )
            {
                $this->assertEquals( $url, $link->getAttribute( 'url' ) );
            }
        }
    }

    /**
     * Test setting of urls in links with object_id attributes
     * @dataProvider providerObjectLinkXmlSample
     * @param $xmlString
     * @param $contentId
     * @param $locationId
     * @param $rawUrl
     * @param $url
     */
    public function testObjectLink( $xmlString, $contentId, $locationId, $rawUrl, $url )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $contentInfo = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\ContentInfo' );
        $location = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' );

        $contentInfo->expects( $this->once() )
            ->method( '__get' )
            ->with( $this->equalTo( 'mainLocationId' ) )
            ->will( $this->returnValue( $locationId ) );

        $contentService->expects( $this->any() )
            ->method( 'loadContentInfo' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $contentInfo ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $urlAliasRouter->expects( $this->once() )
            ->method( 'generate' )
            ->with( $this->equalTo( $location ) )
            ->will( $this->returnValue( $rawUrl ) );

        $converter = new EzLinkToHtml5( $locationService, $contentService, $urlAliasRouter );
        $converter->convert( $xmlDoc );

        $links = $xmlDoc->getElementsByTagName( 'link' );

        foreach ( $links as $link )
        {
            if ( $link->getAttribute( 'object_id' ) == $contentId )
            {
                $this->assertEquals( $url, $link->getAttribute( 'url' ) );
            }
        }
    }

    /**
     * Test logging of bad location links
     * @dataProvider providerBadLocationSample
     * @param $xmlString
     * @param $locationId
     * @param $logMessage
     */
    public function testBadLocationLink( $xmlString, $locationId, $exception, $logType, $logMessage )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();
        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );

        $logger->expects( $this->once() )
            ->method( $logType )
            ->with( $this->equalTo( $logMessage ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->throwException( $exception ) );

        $converter = new EzLinkToHtml5( $locationService, $contentService, $urlAliasRouter, $logger );
        $converter->convert( $xmlDoc );
    }

    /**
     * Test logging of bad object links
     * @dataProvider providerBadObjectSample
     * @param $xmlString
     * @param $contentId
     * @param $exception
     * @param $logType
     * @param $logMessage
     */
    public function testBadObjectLink( $xmlString, $contentId, $exception, $logType, $logMessage )
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML( $xmlString );

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();
        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );

        $logger->expects( $this->once() )
            ->method( $logType )
            ->with( $this->equalTo( $logMessage ) );

        $contentService->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->throwException( $exception ) );

        $converter = new EzLinkToHtml5( $locationService, $contentService, $urlAliasRouter, $logger );
        $converter->convert( $xmlDoc );
    }

}
