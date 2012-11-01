<?php
/**
 * File containing the UrlAliasTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias as UrlAliasMatcher,
    eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest,
    eZ\Publish\API\Repository\Repository;

class UrlAliasTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new UrlAliasMatcher;
    }

    /**
     * @dataProvider setMatchingConfigProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias::setMatchingConfig
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValue::setMatchingConfig
     *
     * @param $matchingConfig
     * @param $expectedValues
     */
    public function testSetMatchingConfig( $matchingConfig, $expectedValues )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame(
            $this->matcher->getValues(),
            $expectedValues
        );
    }

    public function setMatchingConfigProvider()
    {
        return array(
            array( '/foo/bar/', array( 'foo/bar' ) ),
            array( '/foo/bar/', array( 'foo/bar' ) ),
            array( '/foo/bar', array( 'foo/bar' ) ),
            array( array( '/foo/bar/', 'baz/biz/' ), array( 'foo/bar', 'baz/biz' ) ),
            array( array( 'foo/bar', 'baz/biz' ), array( 'foo/bar', 'baz/biz' ) ),
        );
    }

    /**
     * Returns a Repository mock configured to return the appropriate Section object with given section identifier
     *
     * @param $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForUrlAlias( $path )
    {
        // First an url alias that will never match, then the right url alias.
        // This ensures to test even if the location has several url aliases.
        $urlAliasList = array(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias' ),
            $this
                ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias' )
                ->setConstructorArgs( array( array( 'path' => $path ) ) )
                ->getMockForAbstractClass()
        );

        $urlAliasServiceMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\URLAliasService' )
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $urlAliasServiceMock->expects( $this->once() )
            ->method( 'listLocationAliases' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
                $this->isType( 'boolean' )
            )
            ->will( $this->returnValue( $urlAliasList ) )
        ;

        $repository = $this->getRepositoryMock();
        $repository
            ->expects( $this->once() )
            ->method( 'getUrlAliasService' )
            ->will( $this->returnValue( $urlAliasServiceMock ) )
        ;

        return $repository;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias::setMatchingConfig
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
                'foo/url',
                $this->generateRepositoryMockForUrlAlias( 'foo/url' ),
                true
            ),
            array(
                'foo/url',
                $this->generateRepositoryMockForUrlAlias( 'bar/url' ),
                false
            ),
            array(
                array( 'foo/url', 'baz' ),
                $this->generateRepositoryMockForUrlAlias( 'bar/url' ),
                false
            ),
            array(
                array( 'foo/url', 'baz' ),
                $this->generateRepositoryMockForUrlAlias( 'baz' ),
                true
            )
        );
    }

    /**
     * @expectedException \RuntimeException
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\UrlAlias::setMatchingConfig
     */
    public function testMatchContentInfo()
    {
        $this->matcher->setMatchingConfig( 'foo/bar' );
        $this->matcher->matchContentInfo( $this->getContentInfoMock() );
    }
}
