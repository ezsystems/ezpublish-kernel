<?php
/**
 * File containing the ContentTypeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType as ContentTypeIdentifierMatcher;
use eZ\Publish\API\Repository\Repository;

class ContentTypeTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentTypeIdentifierMatcher;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param boolean $expectedResult
     */
    public function testMatchLocation( $matchingConfig, Repository $repository, $expectedResult )
    {
        $this->matcher->setRepository( $repository );
        $this->matcher->setMatchingConfig( $matchingConfig );

        $this->assertSame(
            $expectedResult,
            $this->matcher->matchLocation( $this->generateLocationMock() )
        );
    }

    public function matchLocationProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier( 'foo' ),
            true
        );

        $data[] = array(
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateRepositoryMockForContentTypeIdentifier( 'baz' ),
            true
        );

        return $data;
    }

    /**
     * Generates a Location object in respect of a given content type identifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationMock()
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock( array( 'contentTypeId' => 42 ) )
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param boolean $expectedResult
     */
    public function testMatchContentInfo( $matchingConfig, Repository $repository, $expectedResult )
    {
        $this->matcher->setRepository( $repository );
        $this->matcher->setMatchingConfig( $matchingConfig );

        $this->assertSame(
            $expectedResult,
            $this->matcher->matchContentInfo(
                $this->getContentInfoMock( array( 'contentTypeId' => 42 ) )
            )
        );
    }

    public function matchContentInfoProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier( 'foo' ),
            true
        );

        $data[] = array(
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateRepositoryMockForContentTypeIdentifier( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateRepositoryMockForContentTypeIdentifier( 'baz' ),
            true
        );

        return $data;
    }

    /**
     * Returns a Repository mock configured to return the appropriate ContentType object with given identifier.
     *
     * @param int $contentTypeIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForContentTypeIdentifier( $contentTypeIdentifier )
    {
        $contentTypeMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' )
            ->setConstructorArgs(
                array( array( 'identifier' => $contentTypeIdentifier ) )
            )
            ->getMockForAbstractClass();
        $contentTypeServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\ContentTypeService' )
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeServiceMock->expects( $this->once() )
            ->method( 'loadContentType' )
            ->with( 42 )
            ->will(
                $this->returnValue( $contentTypeMock )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->any() )
            ->method( 'getContentTypeService' )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        return $repository;
    }
}
