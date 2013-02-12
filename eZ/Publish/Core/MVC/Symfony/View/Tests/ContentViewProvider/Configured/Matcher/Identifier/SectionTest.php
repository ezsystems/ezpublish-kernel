<?php
/**
 * File containing the SectionTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\Section as SectionIdentifierMatcher;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\API\Repository\Repository;

class SectionTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\Section
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new SectionIdentifierMatcher;
    }

    /**
     * Returns a Repository mock configured to return the appropriate Section object with given section identifier
     *
     * @param string $sectionIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForSectionIdentifier( $sectionIdentifier )
    {
        $sectionServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\SectionService' )
            ->disableOriginalConstructor()
            ->getMock();
        $sectionServiceMock->expects( $this->once() )
            ->method( 'loadSection' )
            ->will(
                $this->returnValue(
                    $this
                        ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Section' )
                        ->setConstructorArgs(
                            array(
                                array( 'identifier' => $sectionIdentifier )
                            )
                        )
                        ->getMockForAbstractClass()
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->once() )
            ->method( 'getSectionService' )
            ->will( $this->returnValue( $sectionServiceMock ) );

        return $repository;
    }

    /**
     * @dataProvider matchSectionProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\Section::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param boolean $expectedResult
     *
     * @return void
     */
    public function testMatchLocation( $matchingConfig, Repository $repository, $expectedResult )
    {
        $this->matcher->setRepository( $repository );
        $this->matcher->setMatchingConfig( $matchingConfig );
        $location = $this->getLocationMock();
        $location
            ->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock()
                )
            );
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchLocation( $location )
        );
    }

    public function matchSectionProvider()
    {
        return array(
            array(
                'foo',
                $this->generateRepositoryMockForSectionIdentifier( 'foo' ),
                true
            ),
            array(
                'foo',
                $this->generateRepositoryMockForSectionIdentifier( 'bar' ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->generateRepositoryMockForSectionIdentifier( 'bar' ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->generateRepositoryMockForSectionIdentifier( 'baz' ),
                true
            )
        );
    }

    /**
     * @dataProvider matchSectionProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\Section::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param string|string[] $matchingConfig
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
            $this->matcher->matchContentInfo( $this->getContentInfoMock() )
        );
    }
}
