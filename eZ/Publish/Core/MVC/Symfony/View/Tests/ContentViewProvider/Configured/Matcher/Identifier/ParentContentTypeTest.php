<?php
/**
 * File containing the ParentContentTypeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ParentContentType as ParentContentTypeMatcher;
use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\API\Repository\Repository;

class ParentContentTypeTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ParentContentType
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ParentContentTypeMatcher;
    }

    /**
     * Returns a Repository mock configured to return the appropriate Section object with given section identifier
     *
     * @param string $contentTypeIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForContentTypeIdentifier( $contentTypeIdentifier )
    {
        $parentContentInfo = $this->getContentInfoMock( array( "contentTypeId" => 42 ) );
        $parentLocation = $this->getLocationMock();
        $parentLocation->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue( $parentContentInfo )
            );

        $locationServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\LocationService' )
            ->disableOriginalConstructor()
            ->getMock();
        $locationServiceMock->expects( $this->atLeastOnce() )
            ->method( 'loadLocation' )
            ->will(
                $this->returnValue( $parentLocation )
            );
        // The following is used in the case of a match by contentInfo
        $locationServiceMock->expects( $this->any() )
            ->method( 'loadLocation' )
            ->will(
                $this->returnValue( $this->getLocationMock() )
            );

        $contentTypeServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\ContentTypeService' )
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadContentType' )
            ->with( 42 )
            ->will(
                $this->returnValue(
                    $this
                        ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' )
                        ->setConstructorArgs(
                            array(
                                array( 'identifier' => $contentTypeIdentifier )
                            )
                        )
                        ->getMockForAbstractClass()
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationServiceMock ) );
        $repository
            ->expects( $this->once() )
            ->method( 'getContentTypeService' )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        return $repository;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ParentContentType::matchLocation
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
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchLocation( $this->getLocationMock() )
        );
    }

    public function matchLocationProvider()
    {
        return array(
            array(
                'foo',
                $this->generateRepositoryMockForContentTypeIdentifier( 'foo' ),
                true
            ),
            array(
                'foo',
                $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
                false
            ),
            array(
                array( 'foo', 'baz' ),
                $this->generateRepositoryMockForContentTypeIdentifier( 'baz' ),
                true
            )
        );
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ParentContentType::matchContentInfo
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
