<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Mock test case for Content service
 */
class ContentTest extends BaseServiceMockTest
{
    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     */
    public function testLoadVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            array( "loadVersionInfoById" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadVersionInfoById"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadVersionInfo(
            new ContentInfo( array( "id" => 42 ) ),
            7
        );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            array( "loadContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadContent"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( array( "cro-HR" ) ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadContentByContentInfo(
            new ContentInfo( array( "id" => 42 ) ),
            array( "cro-HR" ),
            7
        );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            array( "loadContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadContent"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( array( "cro-HR" ) ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadContentByVersionInfo(
            new VersionInfo(
                array(
                    "contentInfo" => new ContentInfo( array( "id" => 42 ) ),
                    "versionNo" => 7
                )
            ),
            array( "cro-HR" )
        );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedContentService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\ContentService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()
            )
        );
    }
}
