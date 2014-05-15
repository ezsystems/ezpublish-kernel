<?php
/**
 * File containing the EmbedToHtml5 EzXml test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter\EmbedToHtml5;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
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
    public function providerEmbedXmlSampleContent()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="5" custom:offset="3" object_id="104" size="medium" view="embed"/></paragraph></section>',
                104,
                APIVersionInfo::STATUS_DRAFT,
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
                array(
                    array( 'content', 'read', true ),
                    array( 'content', 'versionread', true ),
                )
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed class="itemized_sub_items" custom:limit="5" custom:funkyattrib="3" object_id="107" size="medium" view="embed"/></paragraph></section>',
                107,
                APIVersionInfo::STATUS_DRAFT,
                'embed',
                array(
                    'objectParameters' => array(
                        'size' => 'medium',
                        'funkyattrib' => 3,
                        'limit' => 5,
                    ),
                    'noLayout' => true,
                ),
                array(
                    array( 'content', 'read', false ),
                    array( 'content', 'view_embed', true ),
                    array( 'content', 'versionread', true ),
                )
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph><embed-inline object_id="110" size="small" view="embed-inline"/></paragraph></section>',
                110,
                APIVersionInfo::STATUS_PUBLISHED,
                'embed-inline',
                array(
                    'noLayout' => true,
                    'objectParameters' => array(
                        'size' => 'small'
                    ),
                ),
                array(
                    array( 'content', 'read', true ),
                )
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph><embed align="left" custom:limit="5" custom:offset="0" object_id="113" size="large" view="embed"/></paragraph></section>',
                113,
                APIVersionInfo::STATUS_DRAFT,
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
                array(
                    array( 'content', 'read', true ),
                    array( 'content', 'versionread', true ),
                )
            )
        );
    }
    /**
     * @return array
     */
    public function providerEmbedXmlSampleLocation()
    {
        return array(
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="7" custom:offset="2" node_id="114" size="medium" view="embed"/></paragraph></section>',
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
                array(
                    array( 'content', 'read', true ),
                )
            ),
            array(
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed align="right" class="itemized_sub_items" custom:limit="7" custom:offset="2" node_id="114" size="medium" view="embed"/></paragraph></section>',
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
                array(
                    array( 'content', 'read', false ),
                    array( 'content', 'view_embed', true ),
                )
            ),
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
                APIVersionInfo::STATUS_PUBLISHED,
                'embed',
                array(
                    'noLayout' => true,
                    'objectParameters' => array(
                        'align' => 'right',
                        'size' => 'medium',
                        'limit' => 5,
                        'offset' => 3,
                    ),
                ),
                array(
                    array( 'content', 'read', true ),
                )
            ),
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockViewManager()
    {
        return $this->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Manager' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockContentService()
    {
        return $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\ContentService' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLocationService()
    {
        return $this->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\LocationService' )
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
        $repositoryClass = 'eZ\\Publish\\Core\\Repository\\Repository';
        $repository = $this
            ->getMockBuilder( $repositoryClass )
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods( $repositoryClass ),
                    array( 'sudo' )
                )
            )
            ->getMock();

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
     * @param $status
     * @param $view
     * @param $parameters
     * @param $permissionsMap
     */
    public function runNodeEmbedContent( $xmlString, $contentId, $status, $view, $parameters, $permissionsMap )
    {
        $dom = new \DOMDocument();
        $dom->loadXML( $xmlString );

        $viewManager = $this->getMockViewManager();
        $contentService = $this->getMockContentService();

        $versionInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' );
        $versionInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( $status ) );

        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $content->expects( $this->any() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $versionInfo ) );

        $contentService->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $this->equalTo( $contentId ) )
            ->will( $this->returnValue( $content ) );

        $repository = $this->getMockRepository( $contentService, null );
        foreach ( $permissionsMap as $index => $permissions )
        {
            $repository->expects( $this->at( $index + 1 ) )
                ->method( "canUser" )
                ->with(
                    $permissions[0],
                    $permissions[1],
                    $content,
                    null
                )
                ->will(
                    $this->returnValue( $permissions[2] )
                );
        }

        $viewManager->expects( $this->once() )
            ->method( 'renderContent' )
            ->with(
                $this->equalTo( $content ),
                $this->equalTo( $view ),
                $this->equalTo( $parameters )
            );

        $converter = new EmbedToHtml5(
            $viewManager,
            $repository,
            array( 'view', 'class', 'node_id', 'object_id' )
        );

        $converter->convert( $dom );
    }

    /**
     * @param $xmlString
     * @param $locationId
     * @param $view
     * @param $parameters
     * @param $permissionsMap
     */
    public function runNodeEmbedLocation( $xmlString, $locationId, $view, $parameters, $permissionsMap )
    {
        $dom = new \DOMDocument();
        $dom->loadXML( $xmlString );

        $viewManager = $this->getMockViewManager();
        $locationService = $this->getMockLocationService();

        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $location
            ->expects( $this->atLeastOnce() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( $locationId ) )
            ->will( $this->returnValue( $location ) );

        $repository = $this->getMockRepository( null, $locationService );
        foreach ( $permissionsMap as $index => $permissions )
        {
            $repository->expects( $this->at( $index + 1 ) )
                ->method( "canUser" )
                ->with(
                    $permissions[0],
                    $permissions[1],
                    $contentInfo,
                    $location
                )
                ->will(
                    $this->returnValue( $permissions[2] )
                );
        }

        $viewManager->expects( $this->once() )
            ->method( 'renderLocation' )
            ->with(
                $this->equalTo( $location ),
                $this->equalTo( $view ),
                $this->equalTo( $parameters )
            );

        $converter = new EmbedToHtml5(
            $viewManager,
            $repository,
            array( 'view', 'class', 'node_id', 'object_id' )
        );

        $converter->convert( $dom );
    }

    /**
     * Basic test to see if preconverter will build an embed
     * @dataProvider providerEmbedXmlSampleContent
     */
    public function testProperEmbedsContent( $xmlString, $contentId, $status, $view, $parameters, $permissionsMap )
    {
        $this->runNodeEmbedContent( $xmlString, $contentId, $status, $view, $parameters, $permissionsMap );
    }

    /**
     * Basic test to see if preconverter will build an embed
     * @dataProvider providerEmbedXmlSampleLocation
     */
    public function testProperEmbedsLocation( $xmlString, $locationId, $view, $parameters, $permissionsMap )
    {
        $this->runNodeEmbedLocation( $xmlString, $locationId, $view, $parameters, $permissionsMap );
    }

    /**
     * Ensure converter doesn't pass on non-custom attributes
     * @dataProvider providerEmbedXmlBadSample
     */
    public function testImproperEmbeds( $xmlString, $contentId, $status, $view, $parameters, $permissionsMap )
    {
        $this->runNodeEmbedContent( $xmlString, $contentId, $status, $view, $parameters, $permissionsMap );
    }

    public function providerForTestEmbedContentThrowsUnauthorizedException()
    {
        return array(
            array(
                array(
                    array( 'content', 'read', false ),
                    array( 'content', 'view_embed', false ),
                )
            ),
            array(
                array(
                    array( 'content', 'read', false ),
                    array( 'content', 'view_embed', true ),
                    array( 'content', 'versionread', false ),
                )
            ),
            array(
                array(
                    array( 'content', 'read', true ),
                    array( 'content', 'versionread', false ),
                )
            ),
        );
    }

    /**
     * @dataProvider providerForTestEmbedContentThrowsUnauthorizedException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testEmbedContentThrowsUnauthorizedException( $permissionsMap )
    {
        $dom = new \DOMDocument();
        $dom->loadXML( '<?xml version="1.0" encoding="utf-8"?><section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed view="embed" object_id="42"/></paragraph></section>' );

        $viewManager = $this->getMockViewManager();
        $contentService = $this->getMockContentService();

        $versionInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo' );
        $versionInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( APIVersionInfo::STATUS_DRAFT ) );

        $content = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content' );
        $content->expects( $this->any() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $versionInfo ) );

        $contentService->expects( $this->once() )
            ->method( 'loadContent' )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $content ) );

        $repository = $this->getMockRepository( $contentService, null );
        foreach ( $permissionsMap as $index => $permissions )
        {
            $repository->expects( $this->at( $index + 1 ) )
                ->method( "canUser" )
                ->with(
                    $permissions[0],
                    $permissions[1],
                    $content,
                    null
                )
                ->will(
                    $this->returnValue( $permissions[2] )
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testEmbedLocationThrowsUnauthorizedException()
    {
        $dom = new \DOMDocument();
        $dom->loadXML( '<?xml version="1.0" encoding="utf-8"?><section xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"><paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/"><embed view="embed" node_id="42"/></paragraph></section>' );

        $viewManager = $this->getMockViewManager();
        $locationService = $this->getMockLocationService();

        $contentInfo = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );
        $location = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $location
            ->expects( $this->exactly( 2 ) )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        $locationService->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $location ) );

        $repository = $this->getMockRepository( null, $locationService );
        $repository->expects( $this->at( 1 ) )
            ->method( "canUser" )
            ->with(
                "content", "read", $contentInfo, $location
            )
            ->will(
                $this->returnValue( false )
            );
        $repository->expects( $this->at( 2 ) )
            ->method( "canUser" )
            ->with(
                "content", "view_embed", $contentInfo, $location
            )
            ->will(
                $this->returnValue( false )
            );

        $converter = new EmbedToHtml5(
            $viewManager,
            $repository,
            array( 'view', 'class', 'node_id', 'object_id' )
        );

        $converter->convert( $dom );
    }
}
