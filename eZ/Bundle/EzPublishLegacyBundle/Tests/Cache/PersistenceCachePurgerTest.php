<?php
/**
 * File containing the PersistenceCachePurgeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Cache;

use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class PersistenceCachePurgerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationHandler;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $cachePurger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->cacheService = $this
            ->getMockBuilder( 'Tedivm\\StashBundle\\Service\\CacheService' )
            ->disableOriginalConstructor()
            ->getMock();

        $this->locationHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );

        $this->cachePurger = new PersistenceCachePurger(
            $this->cacheService, $this->locationHandler, $this->logger
        );
    }

    /**
     * Test case for https://jira.ez.no/browse/EZP-20618
     */
    public function testNotFoundLocation()
    {
        $id = 'locationIdThatDoesNotExist';;
        $this->locationHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->will( $this->throwException( new NotFoundException( 'location', $id ) ) );

        $this->logger
            ->expects( $this->once() )
            ->method( 'notice' );

        $this->cachePurger->content( $id );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::isEnabled
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::setIsEnabled
     */
    public function testIsEnabled()
    {
        $this->assertTrue( $this->cachePurger->isEnabled() );
        $this->cachePurger->setIsEnabled( false );
        $this->assertFalse( $this->cachePurger->isEnabled() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::isAllCleared
     */
    public function testClearAll()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' );

        $this->cachePurger->all();
        $this->assertTrue( $this->cachePurger->isAllCleared() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::resetAllCleared
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::isAllCleared
     */
    public function testResetAllCleared()
    {
        $this->assertFalse( $this->cachePurger->isAllCleared() );
        $this->cachePurger->all();
        $this->assertTrue( $this->cachePurger->isAllCleared() );
        $this->cachePurger->resetAllCleared();
        $this->assertFalse( $this->cachePurger->isAllCleared() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentAlreadyCleared()
    {
        $this->cachePurger->all();
        $this->cacheService
            ->expects( $this->never() )
            ->method( 'clear' );
        $this->cachePurger->content();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::setIsEnabled
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentDisabled()
    {
        $this->cachePurger->setIsEnabled( false );
        $this->cacheService
            ->expects( $this->never() )
            ->method( 'clear' );
        $this->cachePurger->content();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::setIsEnabled
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::all
     */
    public function testClearAllDisabled()
    {
        $this->cachePurger->setIsEnabled( false );
        $this->cacheService
            ->expects( $this->never() )
            ->method( 'clear' );
        $this->cachePurger->all();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearAllContent()
    {
        $map = array(
            array( 'content', null ),
            array( 'urlAlias', null ),
            array( 'location', null ),
        );
        $this->cacheService
            ->expects( $this->exactly( count( $map ) ) )
            ->method( 'clear' )
            ->will( $this->returnValueMap( $map ) );
        $this->assertNull( $this->cachePurger->content() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContent()
    {
        $locationId1 = 1;
        $contentId1 = 10;
        $locationId2 = 2;
        $contentId2 = 20;
        $locationId3 = 3;
        $contentId3 = 30;

        $this->locationHandler
            ->expects( $this->exactly( 3 ) )
            ->method( 'load' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $locationId1, $this->buildLocation( $locationId1, $contentId1 ) ),
                        array( $locationId2, $this->buildLocation( $locationId2, $contentId2 ) ),
                        array( $locationId3, $this->buildLocation( $locationId3, $contentId3 ) ),
                    )
                )
            );

        $this->cacheService
            ->expects( $this->any() )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'content', $contentId1, null ),
                        array( 'content', 'info', $contentId1, null ),
                        array( 'content', $contentId2, null ),
                        array( 'content', 'info', $contentId2, null ),
                        array( 'content', $contentId3, null ),
                        array( 'content', 'info', $contentId3, null ),
                        array( 'urlAlias', null ),
                        array( 'location', null ),
                    )
                )
            );

        $locationIds = array( $locationId1, $locationId2, $locationId3 );
        $this->assertSame( $locationIds, $this->cachePurger->content( $locationIds ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearOneContent()
    {
        $locationId = 1;
        $contentId = 10;

        $this->locationHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->will( $this->returnValue( $this->buildLocation( $locationId, $contentId ) ) );
        $this->cacheService
            ->expects( $this->any() )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'content', $contentId, null ),
                        array( 'content', 'info', $contentId, null ),
                        array( 'urlAlias', null ),
                        array( 'location', null ),
                    )
                )
            );

        $this->assertSame( array( $locationId ), $this->cachePurger->content( $locationId ) );
    }

    /**
     * @param $locationId
     * @param $contentId
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    private function buildLocation( $locationId, $contentId )
    {
        return new Location(
            array(
                'id'           => $locationId,
                'contentId'    => $contentId
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::content
     */
    public function testClearContentFail()
    {
        $this->cachePurger->content( new \stdClass );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentTypeAll()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType' );

        $this->cachePurger->contentType();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentType()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 123 );

        $this->cachePurger->contentType( 123 );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentType
     */
    public function testClearContentTypeFail()
    {
        $this->cachePurger->contentType( new \stdClass() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentTypeGroup
     */
    public function testClearContentTypeGroupAll()
    {
        $this->cacheService
            ->expects( $this->exactly( 2 ) )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'contentTypeGroup', null ),
                        array( 'contentType', null ),
                    )
                )
            );

        $this->cachePurger->contentTypeGroup();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentTypeGroup
     */
    public function testClearContentTypeGroup()
    {
        $this->cacheService
            ->expects( $this->exactly( 2 ) )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'contentTypeGroup', 123, null ),
                        array( 'contentType', null ),
                    )
                )
            );

        $this->cachePurger->contentTypeGroup( 123 );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::contentTypeGroup
     */
    public function testClearContentTypeGroupFail()
    {
        $this->cachePurger->contentTypeGroup( new \stdClass() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::section
     */
    public function testClearSectionAll()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'section' );

        $this->cachePurger->section();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::section
     */
    public function testClearSection()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'section', 123 );

        $this->cachePurger->section( 123 );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::section
     */
    public function testClearSectionFail()
    {
        $this->cachePurger->section( new \stdClass() );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::languages
     */
    public function testClearLanguages()
    {
        $languageId1 = 123;
        $languageId2 = 456;
        $languageId3 = 789;

        $this->cacheService
            ->expects( $this->exactly( 3 ) )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $languageId1, null ),
                        array( $languageId2, null ),
                        array( $languageId3, null ),
                    )
                )
            );

        $this->cachePurger->languages( array( $languageId1, $languageId2, $languageId3 ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::languages
     */
    public function testClearOneLanguage()
    {
        $languageId = 123;

        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $languageId, null ),
                    )
                )
            );

        $this->cachePurger->languages( $languageId );
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUserAll()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'user' );

        $this->cachePurger->user();
    }

    /**
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUser()
    {
        $this->cacheService
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'user', 123 );

        $this->cachePurger->user( 123 );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @covers eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger::user
     */
    public function testClearUserFail()
    {
        $this->cachePurger->user( new \stdClass() );
    }
}
