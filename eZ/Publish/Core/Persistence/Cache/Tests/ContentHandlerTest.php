<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\Core\Persistence\Cache\ContentHandler;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for Persistence\Cache\ContentHandler.
 */
class ContentHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    public function providerForUnCachedMethods()
    {
        return array(
            array('create', array(new CreateStruct())),
            array('createDraftFromVersion', array(2, 1, 14)),
            array('copy', array(2, 1)),
            //array( 'load', array( 2, 1, array( 'eng-GB' ) ) ),
            //array( 'load', array( 2, 1 ) ),
            //array( 'loadContentInfo', array( 2 ) ),
            array('loadVersionInfo', array(2, 1)),
            array('loadDraftsForUser', array(14)),
            //array( 'setStatus', array( 2, 0, 1 ) ),
            //array( 'updateMetadata', array( 2, new MetadataUpdateStruct ) ),
            //array( 'updateContent', array( 2, 1, new UpdateStruct ) ),
            //array( 'deleteContent', array( 2 ) ),
            //array( 'deleteVersion', array( 2, 1 ) ),
            array('listVersions', array(2)),
            array('addRelation', array(new RelationCreateStruct())),
            array('removeRelation', array(66, APIRelation::COMMON)),
            array('loadRelations', array(2, 1, 3)),
            array('loadReverseRelations', array(2, 3)),
            //array( 'publish', array( 2, 3, new MetadataUpdateStruct ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler
     */
    public function testUnCachedMethods($method, array $arguments)
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandler));

        $expects = $innerHandler
            ->expects($this->once())
            ->method($method);

        if (isset($arguments[2])) {
            $expects->with($arguments[0], $arguments[1], $arguments[2]);
        } elseif (isset($arguments[1])) {
            $expects->with($arguments[0], $arguments[1]);
        } elseif (isset($arguments[0])) {
            $expects->with($arguments[0]);
        }

        $expects->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentHandler();
        call_user_func_array(array($handler, $method), $arguments);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 2, 1, 'eng-GB|eng-US')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('load')
            ->with(2, 1, array('eng-GB', 'eng-US'))
            ->will(
                $this->returnValue(
                    new Content(
                        array(
                            'fields' => array(),
                            'versionInfo' => new VersionInfo(
                                array(
                                    'versionNo' => 1,
                                    'contentInfo' => new ContentInfo(array('id' => 2)),
                                )
                            ),
                        )
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content'));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->load(2, 1, array('eng-GB', 'eng-US'));
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::load
     */
    public function testLoadHasCache()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 2, 1, ContentHandler::ALL_TRANSLATIONS_KEY)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('contentHandler');

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    new Content(
                        array(
                            'fields' => array(),
                            'versionInfo' => new VersionInfo(
                                array(
                                    'versionNo' => 1,
                                    'contentInfo' => new ContentInfo(array('id' => 2)),
                                )
                            ),
                        )
                    )
                )
            );

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->load(2, 1);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::loadContentInfo
     */
    public function testLoadContentInfoCacheIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 'info', 2)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(2)
            ->will(
                $this->returnValue(
                    new ContentInfo(array('id' => 2))
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo'));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->loadContentInfo(2, 1);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::loadContentInfo
     */
    public function testLoadContentInfoHasCache()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 'info', 2)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('contentHandler');

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    new ContentInfo(array('id' => 2))
                )
            );

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->loadContentInfo(2);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::setStatus
     */
    public function testSetStatus()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('setStatus')
            ->with(2, VersionInfo::STATUS_ARCHIVED, 1)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('content', 2, 1)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->setStatus(2, VersionInfo::STATUS_ARCHIVED, 1);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::setStatus
     */
    public function testSetStatusPublished()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('setStatus')
            ->with(2, VersionInfo::STATUS_PUBLISHED, 1)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 2, 1)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content', 'info', 2)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->setStatus(2, VersionInfo::STATUS_PUBLISHED, 1);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::updateMetadata
     */
    public function testUpdateMetadata()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateMetadata')
            ->with(2, $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\MetadataUpdateStruct'))
            ->will($this->returnValue(new ContentInfo(array('id' => 2))));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 'info', 2)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo'));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->updateMetadata(2, new MetadataUpdateStruct());
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::updateContent
     */
    public function testUpdateContent()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateContent')
            ->with(2, 1, $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\UpdateStruct'))
            ->will(
                $this->returnValue(
                    new Content(
                        array(
                            'fields' => array(),
                            'versionInfo' => new VersionInfo(
                                array(
                                    'versionNo' => 1,
                                    'contentInfo' => new ContentInfo(array('id' => 2)),
                                )
                            ),
                        )
                    )
                )
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('content', 2, 1)
            ->will($this->returnValue(null));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('content', 2, 1, ContentHandler::ALL_TRANSLATIONS_KEY)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content'));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->updateContent(2, 1, new UpdateStruct());
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::deleteContent
     */
    public function testDeleteContent()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->exactly(2))
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with(2, APIRelation::FIELD)
            ->will(
                $this->returnValue(
                    array(
                        new SPIRelation(array('sourceContentId' => 42)),
                    )
                )
            );

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteContent')
            ->with(2)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 42)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content', 2)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('content', 'info', 2)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(3))
            ->method('clear')
            ->with('content', 'info', 'remoteId')
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(4))
            ->method('clear')
            ->with('location', 'subtree')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->deleteContent(2);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::deleteVersion
     */
    public function testDeleteVersion()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteVersion')
            ->with(2, 1)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 2, 1)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content', 'info', 2)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('content', 'info', 'remoteId')
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects($this->at(3))
            ->method('clear')
            ->with('location', 'subtree')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->deleteVersion(2, 1);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler::publish
     */
    public function testPublish()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('publish')
            ->with(2, 1, $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\MetadataUpdateStruct'))
            ->will(
                $this->returnValue(
                    new Content(
                        array(
                            'fields' => array(),
                            'versionInfo' => new VersionInfo(
                                array(
                                    'versionNo' => 1,
                                    'contentInfo' => new ContentInfo(array('id' => 2)),
                                )
                            ),
                        )
                    )
                )
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('content', 2)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('content', 'info', 'remoteId')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('location', 'subtree')
            ->will($this->returnValue(true));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(3))
            ->method('getItem')
            ->with('content', 2, 1, ContentHandler::ALL_TRANSLATIONS_KEY)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content'));

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $cacheItemMock2 = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(4))
            ->method('getItem')
            ->with('content', 'info', 2)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo'));

        $cacheItemMock2
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->contentHandler();
        $handler->publish(2, 1, new MetadataUpdateStruct());
    }
}
