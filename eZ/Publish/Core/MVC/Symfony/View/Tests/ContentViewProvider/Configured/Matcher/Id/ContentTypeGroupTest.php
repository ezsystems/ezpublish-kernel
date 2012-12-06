<?php
/**
 * File containing the ContentTypeGroupTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentTypeGroup as ContentTypeGroupIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class ContentTypeGroupTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentTypeGroup
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentTypeGroupIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentTypeGroup::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param boolean $expectedResult
     */
    public function testMatchLocation( $matchingConfig, Location $location, $expectedResult )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame( $expectedResult, $this->matcher->matchLocation( $location ) );
    }

    public function matchLocationProvider()
    {
        $data = array();

        $data[] = array(
            123,
            $this->generateLocationForContentTypeGroup( 123 ),
            true
        );

        $data[] = array(
            123,
            $this->generateLocationForContentTypeGroup( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateLocationForContentTypeGroup( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateLocationForContentTypeGroup( 789 ),
            true
        );

        return $data;
    }

    /**
     * Generates a Location object in respect of a given content type identifier
     *
     * @param int $contentTypeGroupId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationForContentTypeGroup( $contentTypeGroupId )
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->generateContentInfoForContentTypeGroup( $contentTypeGroupId )
                )
            );

        return $location;
    }

    /**
     * Generates a ContentInfo object in respect of a given content type identifier
     *
     * @param int $contentTypeGroupId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateContentInfoForContentTypeGroup( $contentTypeGroupId )
    {
        // First a group that will never match, then the right group.
        // This ensures to test even if the content type belongs to several groups at once
        $contentTypeGroups = array(
            $this->getMockForAbstractClass( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' ),
            $this
                ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup' )
                ->setConstructorArgs(
                    array( array( 'id' => $contentTypeGroupId ) )
                )
                ->getMockForAbstractClass()
        );

        $contentType = $this->getMockForAbstractClass( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' );
        $contentType
            ->expects( $this->once() )
            ->method( 'getContentTypeGroups' )
            ->will( $this->returnValue( $contentTypeGroups ) );

        $contentInfo = $this->getContentInfoMock();
        $contentInfo
            ->expects( $this->any() )
            ->method( 'getContentType' )
            ->will( $this->returnValue( $contentType ) );

        return $contentInfo;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentTypeGroup::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param boolean $expectedResult
     */
    public function testMatchContentInfo( $matchingConfig, ContentInfo $contentInfo, $expectedResult )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame( $expectedResult, $this->matcher->matchContentInfo( $contentInfo ) );
    }

    public function matchContentInfoProvider()
    {
        $data = array();

        $data[] = array(
            123,
            $this->generateContentInfoForContentTypeGroup( 123 ),
            true
        );

        $data[] = array(
            123,
            $this->generateContentInfoForContentTypeGroup( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateContentInfoForContentTypeGroup( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateContentInfoForContentTypeGroup( 789 ),
            true
        );

        return $data;
    }
}
