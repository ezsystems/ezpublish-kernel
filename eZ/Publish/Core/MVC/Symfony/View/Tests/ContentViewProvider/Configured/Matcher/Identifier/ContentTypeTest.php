<?php
/**
 * File containing the ContentTypeTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType as ContentTypeIdentifierMatcher;

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
     * @param \PHPUnit_Framework_MockObject_MockObject $location
     * @param boolean $expectedResult
     */
    public function testMatchLocation( $matchingConfig, $location, $expectedResult )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame( $expectedResult, $this->matcher->matchLocation( $location ) );
    }

    public function matchLocationProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateLocationForContentType( 'foo' ),
            true
        );

        $data[] = array(
            'foo',
            $this->generateLocationForContentType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateLocationForContentType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateLocationForContentType( 'baz' ),
            true
        );

        return $data;
    }

    /**
     * Generates a Location object in respect of a given content type identifier
     *
     * @param string $contentTypeIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationForContentType( $contentTypeIdentifier )
    {
        $location = $this->getLocationMock();
        $location
            ->expects( $this->any() )
            ->method( 'getContentInfo' )
            ->will(
                $this->returnValue(
                    $this->generateContentInfoForContentType( $contentTypeIdentifier )
                )
            );

        return $location;
    }

    /**
     * Generates a ContentInfo object in respect of a given content type identifier
     *
     * @param string $contentTypeIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateContentInfoForContentType( $contentTypeIdentifier )
    {
        $contentInfo = $this->getContentInfoMock();
        $contentInfo
            ->expects( $this->any() )
            ->method( 'getContentType' )
            ->will(
                $this->returnValue(
                    $this
                        ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType' )
                        ->setConstructorArgs(
                            array( array( 'identifier' => $contentTypeIdentifier ) )
                        )
                        ->getMockForAbstractClass()
                )
            );

        return $contentInfo;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier\ContentType::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued::setMatchingConfig
     *
     * @param string|string[] $matchingConfig
     * @param \PHPUnit_Framework_MockObject_MockObject $contentInfo
     * @param boolean $expectedResult
     */
    public function testMatchContentInfo( $matchingConfig, $contentInfo, $expectedResult )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame( $expectedResult, $this->matcher->matchContentInfo( $contentInfo ) );
    }

    public function matchContentInfoProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateContentInfoForContentType( 'foo' ),
            true
        );

        $data[] = array(
            'foo',
            $this->generateContentInfoForContentType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateContentInfoForContentType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateContentInfoForContentType( 'baz' ),
            true
        );

        return $data;
    }
}
