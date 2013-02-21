<?php
/**
 * File containing the ContentTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Content as ContentIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class ContentTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Content
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Content::matchLocation
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
        return array(
            array(
                123,
                $this->generateLocationForContentId( 123 ),
                true
            ),
            array(
                123,
                $this->generateLocationForContentId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateLocationForContentId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateLocationForContentId( 789 ),
                true
            )
        );
    }

    /**
     * Generates a Location mock in respect of a given content Id
     *
     * @param int $contentId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationForContentId( $contentId )
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock( array( 'id' => $contentId ) )
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Content::matchContentInfo
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
        return array(
            array(
                123,
                $this->getContentInfoMock( array( 'id' => 123 ) ),
                true
            ),
            array(
                123,
                $this->getContentInfoMock( array( 'id' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getContentInfoMock( array( 'id' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getContentInfoMock( array( 'id' => 789 ) ),
                true
            )
        );
    }
}
