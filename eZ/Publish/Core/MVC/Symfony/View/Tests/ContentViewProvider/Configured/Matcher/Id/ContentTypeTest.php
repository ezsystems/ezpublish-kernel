<?php
/**
 * File containing the ContentTypeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentType as ContentTypeIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class ContentTypeTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentType
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentTypeIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentType::matchLocation
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
            $this->generateLocationForContentType( 123 ),
            true
        );

        $data[] = array(
            123,
            $this->generateLocationForContentType( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateLocationForContentType( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateLocationForContentType( 789 ),
            true
        );

        return $data;
    }

    /**
     * Generates a Location object in respect of a given content type identifier
     *
     * @param int $contentTypeId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationForContentType( $contentTypeId )
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->generateContentInfoForContentType( $contentTypeId )
                )
            );

        return $location;
    }

    /**
     * Generates a ContentInfo object in respect of a given content type identifier
     *
     * @param int $contentTypeId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateContentInfoForContentType( $contentTypeId )
    {
        return $this->getContentInfoMock( array( "contentTypeId" => $contentTypeId ) );
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ContentType::matchContentInfo
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
            $this->generateContentInfoForContentType( 123 ),
            true
        );

        $data[] = array(
            123,
            $this->generateContentInfoForContentType( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateContentInfoForContentType( 456 ),
            false
        );

        $data[] = array(
            array( 123, 789 ),
            $this->generateContentInfoForContentType( 789 ),
            true
        );

        return $data;
    }
}
