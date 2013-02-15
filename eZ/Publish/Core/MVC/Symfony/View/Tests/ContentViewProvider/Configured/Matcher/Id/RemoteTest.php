<?php
/**
 * File containing the RemoteTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Remote as RemoteIdMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;

class RemoteTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Remote
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new RemoteIdMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Remote::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
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
                'foo',
                $this->getLocationMock( array( 'remoteId' => 'foo' ) ),
                true
            ),
            array(
                'foo',
                $this->getLocationMock( array( 'remoteId' => 'bar' ) ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->getLocationMock( array( 'remoteId' => 'bar' ) ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->getLocationMock( array( 'remoteId' => 'baz' ) ),
                true
            )
        );
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\Remote::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
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
                'foo',
                $this->getContentInfoMock( array( 'remoteId' => 'foo' ) ),
                true
            ),
            array(
                'foo',
                $this->getContentInfoMock( array( 'remoteId' => 'bar' ) ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->getContentInfoMock( array( 'remoteId' => 'bar' ) ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->getContentInfoMock( array( 'remoteId' => 'baz' ) ),
                true
            )
        );
    }
}
