<?php
/**
 * File containing the EmbedToHtml5 EzXml test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5;
use PHPUnit_Framework_TestCase;

/**
 * Tests the EmbedToHtml5 Preconverter
 * Class EmbedToHtml5Test
 * @package eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter
 */
class EmbedToHtml5Test extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function providerEmbedXmlSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="5" custom:offset="3" object_id="104" size="medium" view="embed"/></paragraph></section>',
                104,
                null,
                'embed',
                array(
                    'objectParameters' => array(
                        'align' => 'right',
                        'size' => 'medium',
                        'offset' => 3,
                        'limit' => 5,
                    ),
                    'noLayout' => true,
                ),
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="7" custom:offset="2" node_id="114" size="medium" view="embed"/></paragraph></section>',
                null,
                114,
                'embed',
                array(
                    'objectParameters' => array(
                        'align' => 'right',
                        'size' => 'medium',
                        'offset' => 2,
                        'limit' => 7,
                    ),
                    'noLayout' => true,
                ),
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed class="itemized_sub_items" custom:limit="5" custom:funkyattrib="3" object_id="107" size="medium" view="embed"/></paragraph></section>',
                107,
                null,
                'embed',
                array(
                    'objectParameters' => array(
                        'size' => 'medium',
                        'funkyattrib' => 3,
                        'limit' => 5,
                    ),
                    'noLayout' => true,
                ),
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph><embed-inline object_id="110" size="small" view="embed-inline"/></paragraph></section>',
                110,
                null,
                'embed-inline',
                array(
                    'noLayout' => true,
                    'objectParameters' => array(
                        'size' => 'small'
                    ),
                )
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph><embed align="left" custom:limit="5" custom:offset="0" object_id="113" size="large" view="embed"/></paragraph></section>',
                113,
                null,
                'embed',
                array(
                    'noLayout' => true,
                    'objectParameters' => array(
                        'align' => 'left',
                        'size' => 'large',
                        'limit' => '5',
                        'offset' => '0',
                    ),
                ),
            )
        );
    }

    /**
     * @return array
     */
    public function providerEmbedXmlBadSample()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="5" custom:offset="3" custom:object_id="105" object_id="104" size="medium" view="embed"/></paragraph></section>',
                104,
                null,
                'embed',
                array(
                    'noLayout' => true,
                    'objectParameters' => array(
                        'align' => 'right',
                        'size' => 'medium',
                        'limit' => 5,
                        'offset' => 3,
                    ),
                )
            ),
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockViewManager()
    {
        return $this->getMockBuilder( 'eZ\Publish\Core\MVC\Symfony\View\Manager' )
            ->disableOriginalConstructor()
            ->getMock();
    }

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
     * @param $contentService
     * @param $locationService
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRepository( $contentService, $locationService )
    {
        $repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );

        $repository->expects( $this->any() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $contentService ) );

        $repository->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationService ) );

        return $repository;
    }

    /**
     * @param $xmlString
     * @param $contentId
     * @param $locationId
     * @param $view
     * @param $parameters
     */
    public function runNodeEmbed($xmlString, $contentId, $locationId, $view, $parameters)
    {
        $dom = new \DOMDocument();
        $dom->loadXML( $xmlString );

        $viewManager = $this->getMockViewManager();
        $contentService = $this->getMockContentService();
        $locationService = $this->getMockLocationService();

        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $location = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Location' );

        $contentService->expects( $this->any() )
            ->method( 'loadContent' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $content ) );

        $locationService->expects( $this->any() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $repository = $this->getMockRepository( $contentService, $locationService );

        if ( $contentId )
        {
            $viewManager->expects( $this->once() )
                ->method( 'renderContent' )
                ->with(
                    $this->equalTo( $content ),
                    $this->equalTo( $view ),
                    $this->equalTo( $parameters )
                );
        }

        if ( $locationId )
        {
            $viewManager->expects( $this->once() )
                ->method( 'renderLocation' )
                ->with(
                    $this->equalTo( $location ),
                    $this->equalTo( $view ),
                    $this->equalTo( $parameters )
                );
        }

        $converter = new EmbedToHtml5(
            $viewManager,
            $repository,
            array( 'view', 'class', 'node_id', 'object_id' )
        );

        $converter->convert( $dom );
    }

    /**
     * Basic test to see if preconverter will build an embed
     * @dataProvider providerEmbedXmlSample
     */
    public function testProperEmbeds($xmlString, $contentId, $locationId, $view, $parameters)
    {
        $this->runNodeEmbed( $xmlString, $contentId, $locationId, $view, $parameters );
    }

    /**
     * Ensure converter doesn't pass on non-custom attributes
     * @dataProvider providerEmbedXmlBadSample
     */
    public function testImproperEmbeds($xmlString, $contentId, $locationId, $view, $parameters)
    {
        $this->runNodeEmbed( $xmlString, $contentId, $locationId, $view, $parameters );
    }
}
