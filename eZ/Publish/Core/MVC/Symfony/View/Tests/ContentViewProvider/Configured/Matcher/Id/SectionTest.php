<?php
/**
 * File containing the SectionTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Section as SectionIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class SectionTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Section
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new SectionIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Section::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
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
                $this->generateLocationForSectionId( 123 ),
                true
            ),
            array(
                123,
                $this->generateLocationForSectionId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateLocationForSectionId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateLocationForSectionId( 789 ),
                true
            )
        );
    }

    /**
     * Generates a Location mock in respect of a given content Id
     *
     * @param int $sectionId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationForSectionId( $sectionId )
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock( array( 'sectionId' => $sectionId ) )
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Section::matchContentInfo
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
                $this->getContentInfoMock( array( 'sectionId' => 123 ) ),
                true
            ),
            array(
                123,
                $this->getContentInfoMock( array( 'sectionId' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getContentInfoMock( array( 'sectionId' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getContentInfoMock( array( 'sectionId' => 789 ) ),
                true
            )
        );
    }
}
