<?php

/**
 * File containing the EzLinkToHtml5 EzXml test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EzLinkToHtml5;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException as APIUnauthorizedException;
use DOMXPath;

/**
 * Tests the EzLinkToHtml5 Preconverter
 * Class EmbedToHtml5Test.
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
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed ezlegacytmp-embed-link-url="/test"/></section>',
                '/test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed ezlegacytmp-embed-link-url="/test" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
                '/test#anchor',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline ezlegacytmp-embed-link-url="/test"/></section>',
                '/test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline ezlegacytmp-embed-link-url="/test" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
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
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed object_id="103" ezlegacytmp-embed-link-object_id="104"/></section>',
                104,
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed object_id="103" ezlegacytmp-embed-link-object_id="104" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
                104,
                106,
                'test',
                'test#anchor',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline object_id="103" ezlegacytmp-embed-link-object_id="104"/></section>',
                104,
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline object_id="103" ezlegacytmp-embed-link-object_id="104" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
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
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed node_id="105" ezlegacytmp-embed-link-node_id="106" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
                106,
                'test',
                'test#anchor',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                'test',
                'test',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline node_id="105" ezlegacytmp-embed-link-node_id="106" ezlegacytmp-embed-link-anchor_name="anchor"/></section>',
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
                new APINotFoundException('Location', 106),
                'warning',
                'While generating links for xmltext, could not locate Location with ID 106',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is a <link node_id="106">node link</link>.</paragraph></section>',
                106,
                new APIUnauthorizedException('Location', 106),
                'notice',
                'While generating links for xmltext, unauthorized to load Location with ID 106',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                new APINotFoundException('Location', 106),
                'warning',
                'While generating links for xmltext, could not locate Location with ID 106',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                new APIUnauthorizedException('Location', 106),
                'notice',
                'While generating links for xmltext, unauthorized to load Location with ID 106',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                new APINotFoundException('Location', 106),
                'warning',
                'While generating links for xmltext, could not locate Location with ID 106',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline node_id="105" ezlegacytmp-embed-link-node_id="106"/></section>',
                106,
                new APIUnauthorizedException('Location', 106),
                'notice',
                'While generating links for xmltext, unauthorized to load Location with ID 106',
            ),
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
                new APINotFoundException('Content', 205),
                'warning',
                'While generating links for xmltext, could not locate Content object with ID 205',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>This is an <link object_id="205">object link</link>.</paragraph></section>',
                205,
                new APIUnauthorizedException('Content', 205),
                'notice',
                'While generating links for xmltext, unauthorized to load Content object with ID 205',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed object_id="204" ezlegacytmp-embed-link-object_id="205"/></section>',
                205,
                new APINotFoundException('Content', 205),
                'warning',
                'While generating links for xmltext, could not locate Content object with ID 205',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed object_id="204" ezlegacytmp-embed-link-object_id="205"/></section>',
                205,
                new APIUnauthorizedException('Content', 205),
                'notice',
                'While generating links for xmltext, unauthorized to load Content object with ID 205',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline object_id="204" ezlegacytmp-embed-link-object_id="205"/></section>',
                205,
                new APINotFoundException('Content', 205),
                'warning',
                'While generating links for xmltext, could not locate Content object with ID 205',
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><embed-inline object_id="204" ezlegacytmp-embed-link-object_id="205"/></section>',
                205,
                new APIUnauthorizedException('Content', 205),
                'notice',
                'While generating links for xmltext, unauthorized to load Content object with ID 205',
            ),
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockContentService()
    {
        return $this->getMockBuilder('eZ\Publish\Core\Repository\ContentService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLocationService()
    {
        return $this->getMockBuilder('eZ\Publish\Core\Repository\LocationService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockURLAliasService()
    {
        return $this->getMockBuilder('eZ\Publish\Core\Repository\URLAliasService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUrlAliasRouter()
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\MVC\\Symfony\\Routing\\UrlAliasRouter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $contentService
     * @param $locationService
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRepository($contentService, $locationService, $urlAliasService)
    {
        $repository = $this->getMock('eZ\Publish\API\Repository\Repository');

        $repository->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($contentService));

        $repository->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($locationService));

        $repository->expects($this->any())
            ->method('getURLAliasService')
            ->will($this->returnValue($urlAliasService));

        return $repository;
    }

    /**
     * Test setting of urls on links with node_id attributes.
     *
     * @dataProvider providerLinkXmlSample
     *
     * @param $xmlString
     * @param $url
     */
    public function testLink($xmlString, $url)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $contentService->expects($this->never())
            ->method($this->anything());

        $locationService->expects($this->never())
            ->method($this->anything());

        $urlAliasRouter->expects($this->never())
            ->method($this->anything());

        $converter = new EzLinkToHtml5($locationService, $contentService, $urlAliasRouter);
        $converter->convert($xmlDoc);

        $xpath = new DOMXPath($xmlDoc);
        $xpathExpression = '//link|//embed|//embed-inline';

        $elements = $xpath->query($xpathExpression);

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            // assumes only one link, or all pointing to same url
            $this->assertEquals($url, $element->getAttribute('url'));
        }
    }

    /**
     * Test setting of urls on links with node_id attributes.
     *
     * @dataProvider providerLocationLinkXmlSample
     *
     * @param $xmlString
     * @param $locationId
     * @param $rawUrl
     * @param $url
     */
    public function testLocationLink($xmlString, $locationId, $rawUrl, $url)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $location = $this->getMock('eZ\Publish\API\Repository\Values\Content\Location');

        $locationService->expects($this->once())
            ->method('loadLocation')
            ->with($this->equalTo($locationId))
            ->will($this->returnValue($location));

        $urlAliasRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($location))
            ->will($this->returnValue($rawUrl));

        $converter = new EzLinkToHtml5($locationService, $contentService, $urlAliasRouter);
        $converter->convert($xmlDoc);

        $xpath = new DOMXPath($xmlDoc);
        $xpathExpression = '//link|//embed|//embed-inline';

        $elements = $xpath->query($xpathExpression);

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $this->assertEquals($url, $element->getAttribute('url'));
        }
    }

    /**
     * Test setting of urls in links with object_id attributes.
     *
     * @dataProvider providerObjectLinkXmlSample
     *
     * @param $xmlString
     * @param $contentId
     * @param $locationId
     * @param $rawUrl
     * @param $url
     */
    public function testObjectLink($xmlString, $contentId, $locationId, $rawUrl, $url)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();

        $contentInfo = $this->getMock('eZ\Publish\API\Repository\Values\Content\ContentInfo');
        $location = $this->getMock('eZ\Publish\API\Repository\Values\Content\Location');

        $contentInfo->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('mainLocationId'))
            ->will($this->returnValue($locationId));

        $contentService->expects($this->any())
            ->method('loadContentInfo')
            ->with($this->equalTo($contentId))
            ->will($this->returnValue($contentInfo));

        $locationService->expects($this->once())
            ->method('loadLocation')
            ->with($this->equalTo($locationId))
            ->will($this->returnValue($location));

        $urlAliasRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($location))
            ->will($this->returnValue($rawUrl));

        $converter = new EzLinkToHtml5($locationService, $contentService, $urlAliasRouter);
        $converter->convert($xmlDoc);

        $xpath = new DOMXPath($xmlDoc);
        $xpathExpression = '//link|//embed|//embed-inline';

        $elements = $xpath->query($xpathExpression);

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $this->assertEquals($url, $element->getAttribute('url'));
        }
    }

    /**
     * Test logging of bad location links.
     *
     * @dataProvider providerBadLocationSample
     *
     * @param $xmlString
     * @param $locationId
     * @param $logMessage
     */
    public function testBadLocationLink($xmlString, $locationId, $exception, $logType, $logMessage)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();
        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $logger->expects($this->once())
            ->method($logType)
            ->with($this->equalTo($logMessage));

        $locationService->expects($this->once())
            ->method('loadLocation')
            ->with($this->equalTo($locationId))
            ->will($this->throwException($exception));

        $converter = new EzLinkToHtml5($locationService, $contentService, $urlAliasRouter, $logger);
        $converter->convert($xmlDoc);
    }

    /**
     * Test logging of bad object links.
     *
     * @dataProvider providerBadObjectSample
     *
     * @param $xmlString
     * @param $contentId
     * @param $exception
     * @param $logType
     * @param $logMessage
     */
    public function testBadObjectLink($xmlString, $contentId, $exception, $logType, $logMessage)
    {
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlString);

        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();
        $urlAliasRouter = $this->getMockUrlAliasRouter();
        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $logger->expects($this->once())
            ->method($logType)
            ->with($this->equalTo($logMessage));

        $contentService->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo($contentId))
            ->will($this->throwException($exception));

        $converter = new EzLinkToHtml5($locationService, $contentService, $urlAliasRouter, $logger);
        $converter->convert($xmlDoc);
    }
}
