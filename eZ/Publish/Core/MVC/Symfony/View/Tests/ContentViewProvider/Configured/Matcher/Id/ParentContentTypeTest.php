<?php
/**
 * File containing the ParentContentTypeTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentContentType as ParentContentTypeMatcher,
    eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest,
    eZ\Publish\API\Repository\Repository;

class ParentContentTypeTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentContentType
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
     * @param $contentTypeId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForContentTypeId( $contentTypeId )
    {
        $parentContentInfo = $this->getContentInfoMock();
        $parentContentInfo->expects( $this->once() )
            ->method( 'getContentType' )
            ->will(
                $this->returnValue(
                    $this
                        ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' )
                        ->setConstructorArgs(
                            array(
                                 array( 'id' => $contentTypeId )
                            )
                        )
                        ->getMockForAbstractClass()
                )
            )
        ;
        $parentLocation = $this->getLocationMock();
        $parentLocation->expects( $this->once() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue( $parentContentInfo )
            )
        ;

        $locationServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\LocationService' )
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $locationServiceMock->expects( $this->atLeastOnce() )
            ->method( 'loadLocation' )
            ->will(
                $this->returnValue( $parentLocation )
            )
        ;
        // The following is used in the case of a match by contentInfo
        $locationServiceMock->expects( $this->any() )
            ->method( 'loadLocation' )
            ->will(
                $this->returnValue( $this->getLocationMock() )
            )
        ;

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $locationServiceMock ) )
        ;

        return $repository;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentContentType::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param $expectedResult
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
                123,
                $this->generateRepositoryMockForContentTypeId( 123 ),
                true
            ),
            array(
                123,
                $this->generateRepositoryMockForContentTypeId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateRepositoryMockForContentTypeId( 456 ),
                false
            ),
            array(
                array( 123, 789 ),
                $this->generateRepositoryMockForContentTypeId( 789 ),
                true
            )
        );
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id\ParentContentType::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param $expectedResult
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
