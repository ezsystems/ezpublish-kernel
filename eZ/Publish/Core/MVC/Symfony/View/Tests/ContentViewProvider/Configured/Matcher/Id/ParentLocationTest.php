<?php
/**
 * File containing the ParentLocationTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentLocation as ParentLocationIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\API\Repository\Repository;

class ParentLocationTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentLocation
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ParentLocationIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentLocation::matchLocation
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
                $this->getLocationMock( array( 'parentLocationId' => 123 ) ),
                true
            ),
            array(
                123,
                $this->getLocationMock( array( 'parentLocationId' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getLocationMock( array( 'parentLocationId' => 456 ) ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->getLocationMock( array( 'parentLocationId' => 789 ) ),
                true
            )
        );
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentLocation::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param int|int[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param boolean $expectedResult
     *
     * @return void
     */
    public function testMatchContentInfo( $matchingConfig, Repository $repository, $expectedResult )
    {
        $this->matcher->setRepository( $repository );
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchContentInfo( $this->getContentInfoMock( array( "mainLocationId" => 42 ) ) )
        );
    }

    public function matchContentInfoProvider()
    {
        return array(
            array(
                123,
                $this->generateRepositoryMockForParentLocationId( 123 ),
                true
            ),
            array(
                123,
                $this->generateRepositoryMockForParentLocationId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateRepositoryMockForParentLocationId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateRepositoryMockForParentLocationId( 789 ),
                true
            )
        );
    }

    /**
     * Returns a Repository mock configured to return the appropriate Location object with given parent location Id
     *
     * @param int $parentLocationId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForParentLocationId( $parentLocationId )
    {
        $locationServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\LocationService' )
            ->disableOriginalConstructor()
            ->getMock();
        $locationServiceMock->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( 42 )
            ->will(
                $this->returnValue(
                    $this->getLocationMock( array( 'parentLocationId' => $parentLocationId ) )
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->once() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationServiceMock ) );

        return $repository;
    }
}
