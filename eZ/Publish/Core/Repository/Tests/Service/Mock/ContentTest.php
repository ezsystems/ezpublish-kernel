<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\ContentTypeService as APIContentTypeService;
use eZ\Publish\API\Repository\LocationService as APILocationService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct as APIContentCreateStruct;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Helper\DomainMapper;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Helper\NameSchemaService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct as SPIContentUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct as SPIContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field as SPIField;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group as SPIObjectStateGroup;
use eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Exception;

/**
 * Mock test case for Content service.
 */
class ContentTest extends BaseServiceMockTest
{
    /**
     * Represents empty Field Value.
     */
    const EMPTY_FIELD_VALUE = 'empty';

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::__construct
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandlerMock */
        $persistenceHandlerMock = $this->getPersistenceMockHandler('Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $fieldTypeRegistryMock = $this->getFieldTypeRegistryMock();
        $settings = ['default_version_archive_limit' => 10];

        $service = new ContentService(
            $repositoryMock,
            $persistenceHandlerMock,
            $domainMapperMock,
            $relationProcessorMock,
            $nameSchemaServiceMock,
            $fieldTypeRegistryMock,
            $settings
        );

        $this->assertAttributeSame(
            $repositoryMock,
            'repository',
            $service
        );

        $this->assertAttributeSame(
            $persistenceHandlerMock,
            'persistenceHandler',
            $service
        );

        $this->assertAttributeSame(
            $domainMapperMock,
            'domainMapper',
            $service
        );

        $this->assertAttributeSame(
            $relationProcessorMock,
            'relationProcessor',
            $service
        );

        $this->assertAttributeSame(
            $nameSchemaServiceMock,
            'nameSchemaService',
            $service
        );

        $this->assertAttributeSame(
            $fieldTypeRegistryMock,
            'fieldTypeRegistry',
            $service
        );

        $this->assertAttributeSame(
            $settings,
            'settings',
            $service
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoById()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService(['loadContentInfo']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->once())
            ->method('isPublished')
            ->willReturn(true);

        $contentServiceMock->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo(42))
            ->will(
                $this->returnValue(
                    new ContentInfo(['currentVersionNo' => 24])
                )
            );

        $contentHandler->expects($this->once())
            ->method('loadVersionInfo')
            ->with(
                $this->equalTo(42),
                $this->equalTo(24)
            )->will(
                $this->returnValue(new SPIVersionInfo())
            );

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with(new SPIVersionInfo())
            ->will($this->returnValue($versionInfoMock));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('read'),
                $this->equalTo($versionInfoMock)
            )->will($this->returnValue(true));

        $result = $contentServiceMock->loadVersionInfoById(42);

        $this->assertEquals($versionInfoMock, $result);
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadVersionInfoByIdThrowsNotFoundException()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(['loadContentInfo']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();

        $contentHandler->expects($this->once())
            ->method('loadVersionInfo')
            ->with(
                $this->equalTo(42),
                $this->equalTo(24)
            )->will(
                $this->throwException(
                    new NotFoundException(
                        'Content',
                        [
                            'contentId' => 42,
                            'versionNo' => 24,
                        ]
                    )
                )
            );

        $contentServiceMock->loadVersionInfoById(42, 24);
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionNonPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->any())
            ->method('isPublished')
            ->willReturn(false);

        $contentHandler->expects($this->once())
            ->method('loadVersionInfo')
            ->with(
                $this->equalTo(42),
                $this->equalTo(24)
            )->will(
                $this->returnValue(new SPIVersionInfo())
            );

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with(new SPIVersionInfo())
            ->will($this->returnValue($versionInfoMock));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('versionread'),
                $this->equalTo($versionInfoMock)
            )->will($this->returnValue(false));

        $contentServiceMock->loadVersionInfoById(42, 24);
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoByIdPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->once())
            ->method('isPublished')
            ->willReturn(true);

        $contentHandler->expects($this->once())
            ->method('loadVersionInfo')
            ->with(
                $this->equalTo(42),
                $this->equalTo(24)
            )->will(
                $this->returnValue(new SPIVersionInfo())
            );

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with(new SPIVersionInfo())
            ->will($this->returnValue($versionInfoMock));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('read'),
                $this->equalTo($versionInfoMock)
            )->will($this->returnValue(true));

        $result = $contentServiceMock->loadVersionInfoById(42, 24);

        $this->assertEquals($versionInfoMock, $result);
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoByIdNonPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->once())
            ->method('isPublished')
            ->willReturn(false);

        $contentHandler->expects($this->once())
            ->method('loadVersionInfo')
            ->with(
                $this->equalTo(42),
                $this->equalTo(24)
            )->will(
                $this->returnValue(new SPIVersionInfo())
            );

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with(new SPIVersionInfo())
            ->will($this->returnValue($versionInfoMock));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('versionread'),
                $this->equalTo($versionInfoMock)
            )->will($this->returnValue(true));

        $result = $contentServiceMock->loadVersionInfoById(42, 24);

        $this->assertEquals($versionInfoMock, $result);
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     * @depends eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest::testLoadVersionInfoById
     * @depends eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdThrowsNotFoundException
     * @depends eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdThrowsUnauthorizedExceptionNonPublishedVersion
     * @depends eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdPublishedVersion
     * @depends eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdNonPublishedVersion
     */
    public function testLoadVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            ['loadVersionInfoById']
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            'loadVersionInfoById'
        )->with(
            $this->equalTo(42),
            $this->equalTo(7)
        )->will(
            $this->returnValue('result')
        );

        $result = $contentServiceMock->loadVersionInfo(
            new ContentInfo(['id' => 42]),
            7
        );

        $this->assertEquals('result', $result);
    }

    public function testLoadContent()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContent']);
        $content = $this->createMock(APIContent::class);
        $versionInfo = $this->createMock(APIVersionInfo::class);
        $content
            ->expects($this->once())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfo));
        $versionInfo
            ->expects($this->once())
            ->method('isPublished')
            ->willReturn(true);
        $contentId = 123;
        $contentService
            ->expects($this->once())
            ->method('internalLoadContent')
            ->with($contentId)
            ->will($this->returnValue($content));

        $repository
            ->expects($this->once())
            ->method('canUser')
            ->with('content', 'read', $content)
            ->will($this->returnValue(true));

        $this->assertSame($content, $contentService->loadContent($contentId));
    }

    public function testLoadContentNonPublished()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContent']);
        $content = $this->createMock(APIContent::class);
        $versionInfo = $this
            ->getMockBuilder(APIVersionInfo::class)
            ->getMockForAbstractClass();
        $content
            ->expects($this->once())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfo));
        $contentId = 123;
        $contentService
            ->expects($this->once())
            ->method('internalLoadContent')
            ->with($contentId)
            ->will($this->returnValue($content));

        $repository
            ->expects($this->exactly(2))
            ->method('canUser')
            ->will(
                $this->returnValueMap(
                    [
                        ['content', 'read', $content, null, true],
                        ['content', 'versionread', $content, null, true],
                    ]
                )
            );

        $this->assertSame($content, $contentService->loadContent($contentId));
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadContentUnauthorized()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContent']);
        $content = $this->createMock(APIContent::class);
        $contentId = 123;
        $contentService
            ->expects($this->once())
            ->method('internalLoadContent')
            ->with($contentId)
            ->will($this->returnValue($content));

        $repository
            ->expects($this->once())
            ->method('canUser')
            ->with('content', 'read', $content)
            ->will($this->returnValue(false));

        $contentService->loadContent($contentId);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadContentNotPublishedStatusUnauthorized()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContent']);
        $content = $this->createMock(APIContent::class);
        $versionInfo = $this
            ->getMockBuilder(APIVersionInfo::class)
            ->getMockForAbstractClass();
        $content
            ->expects($this->once())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfo));
        $contentId = 123;
        $contentService
            ->expects($this->once())
            ->method('internalLoadContent')
            ->with($contentId)
            ->will($this->returnValue($content));

        $repository
            ->expects($this->exactly(2))
            ->method('canUser')
            ->will(
                $this->returnValueMap(
                    [
                        ['content', 'read', $content, null, true],
                        ['content', 'versionread', $content, null, false],
                    ]
                )
            );

        $contentService->loadContent($contentId);
    }

    /**
     * @dataProvider internalLoadContentProvider
     */
    public function testInternalLoadContent($id, $languages, $versionNo, $isRemoteId, $useAlwaysAvailable)
    {
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $realId = $id;

        if ($isRemoteId) {
            $realId = 123;
            $spiContentInfo = new SPIContentInfo(['currentVersionNo' => $versionNo ?: 7, 'id' => $realId]);
            $contentHandler
                ->expects($this->once())
                ->method('loadContentInfoByRemoteId')
                ->with($id)
                ->will($this->returnValue($spiContentInfo));
        } elseif (!empty($languages) && $useAlwaysAvailable) {
            $spiContentInfo = new SPIContentInfo(['alwaysAvailable' => false]);
            $contentHandler
                ->expects($this->once())
                ->method('loadContentInfo')
                ->with($id)
                ->will($this->returnValue($spiContentInfo));
        }

        $spiContent = new SPIContent([
            'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo(['id' => 42, 'contentTypeId' => 123]),
            ]),
        ]);
        $contentHandler
            ->expects($this->once())
            ->method('load')
            ->with($realId, $versionNo, $languages)
            ->willReturn($spiContent);

        $content = $this->mockBuildContentDomainObject($spiContent, $languages);

        $this->assertSame(
            $content,
            $contentService->internalLoadContent($id, $languages, $versionNo, $isRemoteId, $useAlwaysAvailable)
        );
    }

    public function internalLoadContentProvider()
    {
        return [
            [123, null, null, false, false],
            [123, null, 456, false, false],
            [456, null, 123, false, true],
            [456, null, 2, false, false],
            [456, ['eng-GB'], 2, false, true],
            [456, ['eng-GB', 'fre-FR'], null, false, false],
            [456, ['eng-GB', 'fre-FR', 'nor-NO'], 2, false, false],
            // With remoteId
            [123, null, null, true, false],
            ['someRemoteId', null, 456, true, false],
            [456, null, 123, true, false],
            ['someRemoteId', null, 2, true, false],
            ['someRemoteId', ['eng-GB'], 2, true, false],
            [456, ['eng-GB', 'fre-FR'], null, true, false],
            ['someRemoteId', ['eng-GB', 'fre-FR', 'nor-NO'], 2, true, false],
        ];
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testInternalLoadContentNotFound()
    {
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $id = 123;
        $versionNo = 7;
        $languages = null;
        $contentHandler
            ->expects($this->once())
            ->method('load')
            ->with($id, $versionNo, $languages)
            ->will(
                $this->throwException(
                    $this->createMock(APINotFoundException::class)
                )
            );

        $contentService->internalLoadContent($id, $languages, $versionNo);
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            ['loadContent']
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            'loadContent'
        )->with(
            $this->equalTo(42),
            $this->equalTo(['cro-HR']),
            $this->equalTo(7),
            $this->equalTo(false)
        )->will(
            $this->returnValue('result')
        );

        $result = $contentServiceMock->loadContentByContentInfo(
            new ContentInfo(['id' => 42]),
            ['cro-HR'],
            7
        );

        $this->assertEquals('result', $result);
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            ['loadContent']
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            'loadContent'
        )->with(
            $this->equalTo(42),
            $this->equalTo(['cro-HR']),
            $this->equalTo(7),
            $this->equalTo(false)
        )->will(
            $this->returnValue('result')
        );

        $result = $contentServiceMock->loadContentByVersionInfo(
            new VersionInfo(
                [
                    'contentInfo' => new ContentInfo(['id' => 42]),
                    'versionNo' => 7,
                ]
            ),
            ['cro-HR']
        );

        $this->assertEquals('result', $result);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContentInfo']);
        $contentInfo = $this->createMock(APIContentInfo::class);

        $contentInfo->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $contentService->expects($this->once())
            ->method('internalLoadContentInfo')
            ->with(42)
            ->will($this->returnValue($contentInfo));

        $repository->expects($this->once())
            ->method('canUser')
            ->with('content', 'remove')
            ->will($this->returnValue(false));

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent($contentInfo);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
     */
    public function testDeleteContent()
    {
        $repository = $this->getRepositoryMock();

        $repository->expects($this->once())
            ->method('canUser')
            ->with('content', 'remove')
            ->will($this->returnValue(true));

        $contentService = $this->getPartlyMockedContentService(['internalLoadContentInfo']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $urlAliasHandler */
        $urlAliasHandler = $this->getPersistenceMock()->urlAliasHandler();
        /** @var \PHPUnit\Framework\MockObject\MockObject $locationHandler */
        $locationHandler = $this->getPersistenceMock()->locationHandler();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();

        $contentInfo = $this->createMock(APIContentInfo::class);

        $contentService->expects($this->once())
            ->method('internalLoadContentInfo')
            ->with(42)
            ->will($this->returnValue($contentInfo));

        $contentInfo->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $repository->expects($this->once())->method('beginTransaction');

        $spiLocations = [
            new SPILocation(['id' => 1]),
            new SPILocation(['id' => 2]),
        ];
        $locationHandler->expects($this->once())
            ->method('loadLocationsByContent')
            ->with(42)
            ->will($this->returnValue($spiLocations));

        $contentHandler->expects($this->once())
            ->method('deleteContent')
            ->with(42);

        foreach ($spiLocations as $index => $spiLocation) {
            $urlAliasHandler->expects($this->at($index))
                ->method('locationDeleted')
                ->with($spiLocation->id);
        }

        $repository->expects($this->once())->method('commit');

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent($contentInfo);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
     * @expectedException \Exception
     */
    public function testDeleteContentWithRollback()
    {
        $repository = $this->getRepositoryMock();

        $repository->expects($this->once())
            ->method('canUser')
            ->with('content', 'remove')
            ->will($this->returnValue(true));

        $contentService = $this->getPartlyMockedContentService(['internalLoadContentInfo']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $locationHandler */
        $locationHandler = $this->getPersistenceMock()->locationHandler();

        $contentInfo = $this->createMock(APIContentInfo::class);

        $contentService->expects($this->once())
            ->method('internalLoadContentInfo')
            ->with(42)
            ->will($this->returnValue($contentInfo));

        $contentInfo->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $repository->expects($this->once())->method('beginTransaction');

        $locationHandler->expects($this->once())
            ->method('loadLocationsByContent')
            ->with(42)
            ->will($this->throwException(new \Exception()));

        $repository->expects($this->once())->method('rollback');

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent($contentInfo);
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteVersionThrowsBadStateExceptionLastVersion()
    {
        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('canUser')
            ->with('content', 'versionremove')
            ->will($this->returnValue(true));
        $repository
            ->expects($this->never())
            ->method('beginTransaction');

        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $contentInfo = $this->createMock(APIContentInfo::class);
        $versionInfo = $this->createMock(APIVersionInfo::class);

        $contentInfo
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $versionInfo
            ->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['versionNo', 123],
                        ['contentInfo', $contentInfo],
                    ]
                )
            );
        $versionInfo
            ->expects($this->once())
            ->method('isPublished')
            ->willReturn(false);

        $contentHandler
            ->expects($this->once())
            ->method('listVersions')
            ->with(42)
            ->will($this->returnValue(['version']));

        /* @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo */
        $contentService->deleteVersion($versionInfo);
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$contentCreateStruct' is invalid: 'mainLanguageCode' property must be set
     */
    public function testCreateContentThrowsInvalidArgumentExceptionMainLanguageCodeNotSet()
    {
        $mockedService = $this->getPartlyMockedContentService();
        $mockedService->createContent(new ContentCreateStruct(), []);
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$contentCreateStruct' is invalid: 'contentType' property must be set
     */
    public function testCreateContentThrowsInvalidArgumentExceptionContentTypeNotSet()
    {
        $mockedService = $this->getPartlyMockedContentService();
        $mockedService->createContent(
            new ContentCreateStruct(['mainLanguageCode' => 'eng-US']),
            []
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => [],
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'ownerId' => 169,
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-US',
                'contentType' => $contentType,
            ]
        );

        $repositoryMock->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(169)));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(123))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(get_class($contentCreateStruct)),
                $this->equalTo([])
            )->will($this->returnValue(false));

        $mockedService->createContent(
            new ContentCreateStruct(
                [
                    'mainLanguageCode' => 'eng-US',
                    'contentType' => $contentType,
                ]
            ),
            []
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @exceptionMessage Argument '$contentCreateStruct' is invalid: Another content with remoteId 'faraday' exists
     */
    public function testCreateContentThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContentByRemoteId']);
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => [],
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'ownerId' => 169,
                'alwaysAvailable' => false,
                'remoteId' => 'faraday',
                'mainLanguageCode' => 'eng-US',
                'contentType' => $contentType,
            ]
        );

        $repositoryMock->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(169)));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(123))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(get_class($contentCreateStruct)),
                $this->equalTo([])
            )->will($this->returnValue(true));

        $mockedService->expects($this->once())
            ->method('loadContentByRemoteId')
            ->with($contentCreateStruct->remoteId)
            ->will($this->returnValue('Hello...'));

        $mockedService->createContent(
            new ContentCreateStruct(
                [
                    'remoteId' => 'faraday',
                    'mainLanguageCode' => 'eng-US',
                    'contentType' => $contentType,
                ]
            ),
            []
        );
    }

    /**
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    protected function mapStructFieldsForCreate($mainLanguageCode, $structFields, $fieldDefinitions)
    {
        $mappedFieldDefinitions = [];
        foreach ($fieldDefinitions as $fieldDefinition) {
            $mappedFieldDefinitions[$fieldDefinition->identifier] = $fieldDefinition;
        }

        $mappedStructFields = [];
        foreach ($structFields as $structField) {
            if ($structField->languageCode === null) {
                $languageCode = $mainLanguageCode;
            } else {
                $languageCode = $structField->languageCode;
            }

            $mappedStructFields[$structField->fieldDefIdentifier][$languageCode] = (string)$structField->value;
        }

        return $mappedStructFields;
    }

    /**
     * Returns full, possibly redundant array of field values, indexed by field definition
     * identifier and language code.
     *
     * @throws \RuntimeException Method is intended to be used only with consistent fixtures
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param array $languageCodes
     *
     * @return array
     */
    protected function determineValuesForCreate(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions,
        array $languageCodes
    ) {
        $mappedStructFields = $this->mapStructFieldsForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );

        $values = [];

        foreach ($fieldDefinitions as $fieldDefinition) {
            $identifier = $fieldDefinition->identifier;
            foreach ($languageCodes as $languageCode) {
                if (!$fieldDefinition->isTranslatable) {
                    if (isset($mappedStructFields[$identifier][$mainLanguageCode])) {
                        $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$mainLanguageCode];
                    } else {
                        $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
                    }
                    continue;
                }

                if (isset($mappedStructFields[$identifier][$languageCode])) {
                    $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$languageCode];
                    continue;
                }

                $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
            }
        }

        return $this->stubValues($values);
    }

    /**
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     *
     * @return string[]
     */
    protected function determineLanguageCodesForCreate($mainLanguageCode, array $structFields)
    {
        $languageCodes = [];

        foreach ($structFields as $field) {
            if ($field->languageCode === null || isset($languageCodes[$field->languageCode])) {
                continue;
            }

            $languageCodes[$field->languageCode] = true;
        }

        $languageCodes[$mainLanguageCode] = true;

        return array_keys($languageCodes);
    }

    /**
     * Asserts that calling createContent() with given API field set causes calling
     * Handler::createContent() with given SPI field set.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[] $objectStateGroups
     * @param bool $execute
     *
     * @return mixed
     */
    protected function assertForTestCreateContentNonRedundantFieldSet(
        $mainLanguageCode,
        array $structFields,
        array $spiFields,
        array $fieldDefinitions,
        array $locationCreateStructs = [],
        $withObjectStates = false,
        $execute = true
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        /** @var \PHPUnit\Framework\MockObject\MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $languageCodes = $this->determineLanguageCodesForCreate($mainLanguageCode, $structFields);
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
                'nameSchema' => '<nameSchema>',
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'fields' => $structFields,
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'alwaysAvailable' => false,
                'ownerId' => 169,
                'sectionId' => 1,
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $repositoryMock->expects($this->once())->method('beginTransaction');

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentType->id))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $that = $this;
        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(APIContentCreateStruct::class),
                $this->equalTo($locationCreateStructs)
            )->will(
                $this->returnCallback(
                    function () use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, func_get_arg(2));

                        return true;
                    }
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('getUniqueHash')
            ->with($this->isInstanceOf(APIContentCreateStruct::class))
            ->will(
                $this->returnCallback(
                    function ($object) use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, $object);

                        return 'hash';
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('acceptValue')
            ->will(
                $this->returnCallback(
                    function ($valueString) {
                        return new ValueStub($valueString);
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('toPersistenceValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) {
                        return (string)$value;
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects($this->any())
            ->method('isEmptyValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) use ($emptyValue) {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('validate')
            ->will($this->returnValue([]));

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        $relationProcessorMock
            ->expects($this->exactly(count($fieldDefinitions) * count($languageCodes)))
            ->method('appendFieldRelations')
            ->with(
                $this->isType('array'),
                $this->isType('array'),
                $this->isInstanceOf(SPIFieldType::class),
                $this->isInstanceOf(Value::class),
                $this->anything()
            );

        $values = $this->determineValuesForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions,
            $languageCodes
        );
        $nameSchemaServiceMock->expects($this->once())
            ->method('resolve')
            ->with(
                $this->equalTo($contentType->nameSchema),
                $this->equalTo($contentType),
                $this->equalTo($values),
                $this->equalTo($languageCodes)
            )->will($this->returnValue([]));

        $relationProcessorMock->expects($this->any())
            ->method('processFieldRelations')
            ->with(
                $this->isType('array'),
                $this->equalTo(42),
                $this->isType('int'),
                $this->equalTo($contentType),
                $this->equalTo([])
            );

        if (!$withObjectStates) {
            $objectStateHandlerMock->expects($this->once())
                ->method('loadAllGroups')
                ->will($this->returnValue([]));
        }

        if ($execute) {
            $spiContentCreateStruct = new SPIContentCreateStruct(
                [
                    'name' => [],
                    'typeId' => 123,
                    'sectionId' => 1,
                    'ownerId' => 169,
                    'remoteId' => 'hash',
                    'fields' => $spiFields,
                    'modified' => time(),
                    'initialLanguageId' => 4242,
                ]
            );
            $spiContentCreateStruct2 = clone $spiContentCreateStruct;
            ++$spiContentCreateStruct2->modified;

            $spiContent = new SPIContent(
                [
                    'versionInfo' => new SPIContent\VersionInfo(
                        [
                            'contentInfo' => new SPIContent\ContentInfo(['id' => 42]),
                            'versionNo' => 7,
                        ]
                    ),
                ]
            );

            $contentHandlerMock->expects($this->once())
                ->method('create')
                ->with($this->logicalOr($spiContentCreateStruct, $spiContentCreateStruct2))
                ->will($this->returnValue($spiContent));

            $repositoryMock->expects($this->once())->method('commit');
            $domainMapperMock->expects($this->once())
                ->method('buildContentDomainObject')
                ->with(
                    $this->isInstanceOf(SPIContent::class),
                    $this->equalTo($contentType)
                );

            $mockedService->createContent($contentCreateStruct, []);
        }

        return $contentCreateStruct;
    }

    public function providerForTestCreateContentNonRedundantFieldSet1()
    {
        $spiFields = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue',
                    'languageCode' => 'eng-US',
                ]
            ),
        ];

        return [
            // 0. Without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields,
            ],
            // 1. Without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields,
            ],
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * Testing the simplest use case.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSet1
     */
    public function testCreateContentNonRedundantFieldSet1($mainLanguageCode, $structFields, $spiFields)
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentNonRedundantFieldSet2()
    {
        $spiFields = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                ]
            ),
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'ger-DE',
                ]
            ),
        ];

        return [
            // 0. With language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'ger-DE',
                        ]
                    ),
                ],
                $spiFields,
            ],
            // 1. Without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'ger-DE',
                        ]
                    ),
                ],
                $spiFields,
            ],
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * Testing multiple languages with multiple translatable fields with empty default value.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSet2
     */
    public function testCreateContentNonRedundantFieldSet2($mainLanguageCode, $structFields, $spiFields)
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier1',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId2',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier2',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
        ];

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentNonRedundantFieldSetComplex()
    {
        $spiFields0 = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue2',
                    'languageCode' => 'eng-US',
                ]
            ),
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue4',
                    'languageCode' => 'eng-US',
                ]
            ),
        ];
        $spiFields1 = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'ger-DE',
                ]
            ),
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue2',
                    'languageCode' => 'ger-DE',
                ]
            ),
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-US',
                ]
            ),
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue4',
                    'languageCode' => 'eng-US',
                ]
            ),
        ];

        return [
            // 0. Creating by default values only
            [
                'eng-US',
                [],
                $spiFields0,
            ],
            // 1. Multiple languages with language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'ger-DE',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier4',
                            'value' => 'newValue4',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 2. Multiple languages without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'ger-DE',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier4',
                            'value' => 'newValue4',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields1,
            ],
        ];
    }

    protected function fixturesForTestCreateContentNonRedundantFieldSetComplex()
    {
        return [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier1',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId2',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier2',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue2',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId3',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier3',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId4',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier4',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue4',
                ]
            ),
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * Testing multiple languages with multiple translatable fields with empty default value.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSetComplex
     */
    public function testCreateContentNonRedundantFieldSetComplex($mainLanguageCode, $structFields, $spiFields)
    {
        $fieldDefinitions = $this->fixturesForTestCreateContentNonRedundantFieldSetComplex();

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentWithInvalidLanguage()
    {
        return [
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'Klingon',
                        ]
                    ),
                ],
            ],
            [
                'Klingon',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentWithInvalidLanguage
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'Language' with identifier 'Klingon'
     */
    public function testCreateContentWithInvalidLanguage($mainLanguageCode, $structFields)
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => [],
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'fields' => $structFields,
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'alwaysAvailable' => false,
                'ownerId' => 169,
                'sectionId' => 1,
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function ($languageCode) {
                        if ($languageCode === 'Klingon') {
                            throw new NotFoundException('Language', 'Klingon');
                        }

                        return new Language(['id' => 4242]);
                    }
                )
            );

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentType->id))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $that = $this;
        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(APIContentCreateStruct::class),
                $this->equalTo([])
            )->will(
                $this->returnCallback(
                    function () use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, func_get_arg(2));

                        return true;
                    }
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('getUniqueHash')
            ->with($this->isInstanceOf(APIContentCreateStruct::class))
            ->will(
                $this->returnCallback(
                    function ($object) use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, $object);

                        return 'hash';
                    }
                )
            );

        $mockedService->createContent($contentCreateStruct, []);
    }

    protected function assertForCreateContentContentValidationException(
        $mainLanguageCode,
        $structFields,
        $fieldDefinitions = []
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContentByRemoteId']);
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'ownerId' => 169,
                'alwaysAvailable' => false,
                'remoteId' => 'faraday',
                'mainLanguageCode' => $mainLanguageCode,
                'fields' => $structFields,
                'contentType' => $contentType,
            ]
        );

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(123))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(get_class($contentCreateStruct)),
                $this->equalTo([])
            )->will($this->returnValue(true));

        $mockedService->expects($this->once())
            ->method('loadContentByRemoteId')
            ->with($contentCreateStruct->remoteId)
            ->will(
                $this->throwException(new NotFoundException('Content', 'faraday'))
            );

        $mockedService->createContent($contentCreateStruct, []);
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionFieldDefinition()
    {
        return [
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage Field definition 'identifier' does not exist in given ContentType
     */
    public function testCreateContentThrowsContentValidationExceptionFieldDefinition($mainLanguageCode, $structFields)
    {
        $this->assertForCreateContentContentValidationException(
            $mainLanguageCode,
            $structFields,
            []
        );
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionTranslation()
    {
        return [
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage A value is set for non translatable field definition 'identifier' with language 'eng-US'
     */
    public function testCreateContentThrowsContentValidationExceptionTranslation($mainLanguageCode, $structFields)
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
        ];

        $this->assertForCreateContentContentValidationException(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );
    }

    /**
     * Asserts behaviour necessary for testing ContentFieldValidationException because of required
     * field being empty.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return mixed
     */
    protected function assertForTestCreateContentRequiredField(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions
    ) {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
                'nameSchema' => '<nameSchema>',
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'fields' => $structFields,
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'alwaysAvailable' => false,
                'ownerId' => 169,
                'sectionId' => 1,
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentType->id))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $that = $this;
        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(APIContentCreateStruct::class),
                $this->equalTo([])
            )->will(
                $this->returnCallback(
                    function () use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, func_get_arg(2));

                        return true;
                    }
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('getUniqueHash')
            ->with($this->isInstanceOf(APIContentCreateStruct::class))
            ->will(
                $this->returnCallback(
                    function ($object) use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, $object);

                        return 'hash';
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('acceptValue')
            ->will(
                $this->returnCallback(
                    function ($valueString) {
                        return new ValueStub($valueString);
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects($this->any())
            ->method('isEmptyValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) use ($emptyValue) {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('validate')
            ->will($this->returnValue([]));

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        return $contentCreateStruct;
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionRequiredField()
    {
        return [
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null,
                        ]
                    ),
                ],
                'identifier',
                'eng-US',
            ],
        ];
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionRequiredField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateContentRequiredField(
        $mainLanguageCode,
        $structFields,
        $identifier,
        $languageCode
    ) {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier',
                    'isRequired' => true,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];
        $contentCreateStruct = $this->assertForTestCreateContentRequiredField(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );

        $mockedService = $this->getPartlyMockedContentService();

        try {
            $mockedService->createContent($contentCreateStruct, []);
        } catch (ContentValidationException $e) {
            $this->assertEquals(
                "Value for required field definition '{$identifier}' with language '{$languageCode}' is empty",
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Asserts behaviour necessary for testing ContentFieldValidationException because of
     * field not being valid.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return mixed
     */
    protected function assertForTestCreateContentThrowsContentFieldValidationException(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions
    ) {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $languageCodes = $this->determineLanguageCodesForCreate($mainLanguageCode, $structFields);
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
                'nameSchema' => '<nameSchema>',
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'fields' => $structFields,
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'alwaysAvailable' => false,
                'ownerId' => 169,
                'sectionId' => 1,
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentType->id))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $that = $this;
        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(APIContentCreateStruct::class),
                $this->equalTo([])
            )->will(
                $this->returnCallback(
                    function () use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, func_get_arg(2));

                        return true;
                    }
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('getUniqueHash')
            ->with($this->isInstanceOf(APIContentCreateStruct::class))
            ->will(
                $this->returnCallback(
                    function ($object) use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, $object);

                        return 'hash';
                    }
                )
            );

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        $relationProcessorMock
            ->expects($this->any())
            ->method('appendFieldRelations')
            ->with(
                $this->isType('array'),
                $this->isType('array'),
                $this->isInstanceOf(SPIFieldType::class),
                $this->isInstanceOf(Value::class),
                $this->anything()
            );

        $fieldValues = $this->determineValuesForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions,
            $languageCodes
        );
        $allFieldErrors = [];
        $validateCount = 0;
        $emptyValue = self::EMPTY_FIELD_VALUE;
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            foreach ($fieldValues[$fieldDefinition->identifier] as $languageCode => $value) {
                $fieldTypeMock->expects($this->at($validateCount++))
                    ->method('acceptValue')
                    ->will(
                        $this->returnCallback(
                            function ($valueString) {
                                return new ValueStub($valueString);
                            }
                        )
                    );

                $fieldTypeMock->expects($this->at($validateCount++))
                    ->method('isEmptyValue')
                    ->will(
                        $this->returnCallback(
                            function (ValueStub $value) use ($emptyValue) {
                                return $emptyValue === (string)$value;
                            }
                        )
                    );

                if (self::EMPTY_FIELD_VALUE === (string)$value) {
                    continue;
                }

                $fieldTypeMock->expects($this->at($validateCount++))
                    ->method('validate')
                    ->with(
                        $this->equalTo($fieldDefinition),
                        $this->equalTo($value)
                    )->will($this->returnArgument(1));

                $allFieldErrors[$fieldDefinition->id][$languageCode] = $value;
            }
        }

        return [$contentCreateStruct, $allFieldErrors];
    }

    public function providerForTestCreateContentThrowsContentFieldValidationException()
    {
        return $this->providerForTestCreateContentNonRedundantFieldSetComplex();
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @expectedExceptionMessage Content fields did not validate
     */
    public function testCreateContentThrowsContentFieldValidationException($mainLanguageCode, $structFields)
    {
        $fieldDefinitions = $this->fixturesForTestCreateContentNonRedundantFieldSetComplex();
        list($contentCreateStruct, $allFieldErrors) =
            $this->assertForTestCreateContentThrowsContentFieldValidationException(
                $mainLanguageCode,
                $structFields,
                $fieldDefinitions
            );

        $mockedService = $this->getPartlyMockedContentService();

        try {
            $mockedService->createContent($contentCreateStruct);
        } catch (ContentFieldValidationException $e) {
            $this->assertEquals($allFieldErrors, $e->getFieldErrors());
            throw $e;
        }
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::buildSPILocationCreateStructs
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     */
    public function testCreateContentWithLocations()
    {
        $spiFields = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue',
                    'languageCode' => 'eng-US',
                ]
            ),
        ];
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        // Set up a simple case that will pass
        $locationCreateStruct1 = new LocationCreateStruct(['parentLocationId' => 321]);
        $locationCreateStruct2 = new LocationCreateStruct(['parentLocationId' => 654]);
        $locationCreateStructs = [$locationCreateStruct1, $locationCreateStruct2];
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            'eng-US',
            [],
            $spiFields,
            $fieldDefinitions,
            $locationCreateStructs,
            false,
            // Do not execute
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $locationServiceMock = $this->getLocationServiceMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $spiLocationCreateStruct = new SPILocation\CreateStruct();
        $parentLocation = new Location(['contentInfo' => new ContentInfo(['sectionId' => 1])]);

        $locationServiceMock->expects($this->at(0))
            ->method('loadLocation')
            ->with($this->equalTo(321))
            ->will($this->returnValue($parentLocation));

        $locationServiceMock->expects($this->at(1))
            ->method('loadLocation')
            ->with($this->equalTo(654))
            ->will($this->returnValue($parentLocation));

        $repositoryMock->expects($this->atLeastOnce())
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock));

        $domainMapperMock->expects($this->at(1))
            ->method('buildSPILocationCreateStruct')
            ->with(
                $this->equalTo($locationCreateStruct1),
                $this->equalTo($parentLocation),
                $this->equalTo(true),
                $this->equalTo(null),
                $this->equalTo(null)
            )->will($this->returnValue($spiLocationCreateStruct));

        $domainMapperMock->expects($this->at(2))
            ->method('buildSPILocationCreateStruct')
            ->with(
                $this->equalTo($locationCreateStruct2),
                $this->equalTo($parentLocation),
                $this->equalTo(false),
                $this->equalTo(null),
                $this->equalTo(null)
            )->will($this->returnValue($spiLocationCreateStruct));

        $spiContentCreateStruct = new SPIContentCreateStruct(
            [
                'name' => [],
                'typeId' => 123,
                'sectionId' => 1,
                'ownerId' => 169,
                'remoteId' => 'hash',
                'fields' => $spiFields,
                'modified' => time(),
                'initialLanguageId' => 4242,
                'locations' => [$spiLocationCreateStruct, $spiLocationCreateStruct],
            ]
        );
        $spiContentCreateStruct2 = clone $spiContentCreateStruct;
        ++$spiContentCreateStruct2->modified;

        $spiContent = new SPIContent(
            [
                'versionInfo' => new SPIContent\VersionInfo(
                    [
                        'contentInfo' => new SPIContent\ContentInfo(['id' => 42]),
                        'versionNo' => 7,
                    ]
                ),
            ]
        );

        $handlerMock->expects($this->once())
            ->method('create')
            ->with($this->logicalOr($spiContentCreateStruct, $spiContentCreateStruct2))
            ->will($this->returnValue($spiContent));

        $domainMapperMock->expects($this->once())
            ->method('buildContentDomainObject')
            ->with(
                $this->isInstanceOf(SPIContent::class),
                $this->isInstanceOf(APIContentType::class)
            );

        $repositoryMock->expects($this->once())->method('commit');

        // Execute
        $mockedService->createContent($contentCreateStruct, $locationCreateStructs);
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::buildSPILocationCreateStructs
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Multiple LocationCreateStructs with the same parent Location '321' are given
     */
    public function testCreateContentWithLocationsDuplicateUnderParent()
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $locationServiceMock = $this->getLocationServiceMock();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $spiLocationCreateStruct = new SPILocation\CreateStruct();
        $parentLocation = new Location(['id' => 321]);
        $locationCreateStruct = new LocationCreateStruct(['parentLocationId' => 321]);
        $locationCreateStructs = [$locationCreateStruct, clone $locationCreateStruct];
        $contentType = new ContentType(
            [
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
                'nameSchema' => '<nameSchema>',
            ]
        );
        $contentCreateStruct = new ContentCreateStruct(
            [
                'fields' => [],
                'mainLanguageCode' => 'eng-US',
                'contentType' => $contentType,
                'alwaysAvailable' => false,
                'ownerId' => 169,
                'sectionId' => 1,
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentType->id))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $that = $this;
        $repositoryMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('create'),
                $this->isInstanceOf(APIContentCreateStruct::class),
                $this->equalTo($locationCreateStructs)
            )->will(
                $this->returnCallback(
                    function () use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, func_get_arg(2));

                        return true;
                    }
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('getUniqueHash')
            ->with($this->isInstanceOf(APIContentCreateStruct::class))
            ->will(
                $this->returnCallback(
                    function ($object) use ($that, $contentCreateStruct) {
                        $that->assertEquals($contentCreateStruct, $object);

                        return 'hash';
                    }
                )
            );

        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with($this->equalTo(321))
            ->will($this->returnValue($parentLocation));

        $repositoryMock->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock));

        $domainMapperMock->expects($this->any())
            ->method('buildSPILocationCreateStruct')
            ->with(
                $this->equalTo($locationCreateStruct),
                $this->equalTo($parentLocation),
                $this->equalTo(true),
                $this->equalTo(null),
                $this->equalTo(null)
            )->will($this->returnValue($spiLocationCreateStruct));

        $mockedService->createContent(
            $contentCreateStruct,
            $locationCreateStructs
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     */
    public function testCreateContentObjectStates()
    {
        $spiFields = [
            new SPIField(
                [
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue',
                    'languageCode' => 'eng-US',
                ]
            ),
        ];
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];
        $objectStateGroups = [
            new SPIObjectStateGroup(['id' => 10]),
            new SPIObjectStateGroup(['id' => 20]),
        ];

        // Set up a simple case that will pass
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            'eng-US',
            [],
            $spiFields,
            $fieldDefinitions,
            [],
            true,
            // Do not execute
            false
        );
        $timestamp = time();
        $contentCreateStruct->modificationDate = new \DateTime("@{$timestamp}");

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $spiContentCreateStruct = new SPIContentCreateStruct(
            [
                'name' => [],
                'typeId' => 123,
                'sectionId' => 1,
                'ownerId' => 169,
                'remoteId' => 'hash',
                'fields' => $spiFields,
                'modified' => $timestamp,
                'initialLanguageId' => 4242,
                'locations' => [],
            ]
        );
        $spiContentCreateStruct2 = clone $spiContentCreateStruct;
        ++$spiContentCreateStruct2->modified;

        $spiContent = new SPIContent(
            [
                'versionInfo' => new SPIContent\VersionInfo(
                    [
                        'contentInfo' => new SPIContent\ContentInfo(['id' => 42]),
                        'versionNo' => 7,
                    ]
                ),
            ]
        );

        $handlerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($spiContentCreateStruct))
            ->will($this->returnValue($spiContent));

        $domainMapperMock->expects($this->once())
            ->method('buildContentDomainObject')
            ->with(
                $this->isInstanceOf(SPIContent::class),
                $this->isInstanceOf(APIContentType::class)
            );

        $repositoryMock->expects($this->once())->method('commit');

        // Execute
        $mockedService->createContent($contentCreateStruct, []);
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionTranslation
     * @expectedException \Exception
     * @expectedExceptionMessage Store failed
     */
    public function testCreateContentWithRollback()
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        // Setup a simple case that will pass
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            'eng-US',
            [],
            [],
            $fieldDefinitions,
            [],
            false,
            // Do not execute test
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects($this->never())->method('commit');
        $repositoryMock->expects($this->once())->method('rollback');

        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $contentHandlerMock->expects($this->once())
            ->method('create')
            ->with($this->anything())
            ->will($this->throwException(new \Exception('Store failed')));

        // Execute
        $this->partlyMockedContentService->createContent($contentCreateStruct, []);
    }

    public function providerForTestUpdateContentThrowsBadStateException()
    {
        return [
            [VersionInfo::STATUS_PUBLISHED],
            [VersionInfo::STATUS_ARCHIVED],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @dataProvider providerForTestUpdateContentThrowsBadStateException
     */
    public function testUpdateContentThrowsBadStateException($status)
    {
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        $contentUpdateStruct = new ContentUpdateStruct();
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(['id' => 42]),
                'versionNo' => 7,
                'status' => $status,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => [],
            ]
        );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $mockedService->updateContent($versionInfo, $contentUpdateStruct);
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $permissionResolverMock = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        $contentUpdateStruct = new ContentUpdateStruct();
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(['id' => 42]),
                'versionNo' => 7,
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => [],
            ]
        );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(false));

        $mockedService->updateContent($versionInfo, $contentUpdateStruct);
    }

    /**
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param string[] $existingLanguages
     *
     * @return string[]
     */
    protected function determineLanguageCodesForUpdate($initialLanguageCode, array $structFields, $existingLanguages)
    {
        $languageCodes = array_fill_keys($existingLanguages, true);
        if ($initialLanguageCode !== null) {
            $languageCodes[$initialLanguageCode] = true;
        }

        foreach ($structFields as $field) {
            if ($field->languageCode === null || isset($languageCodes[$field->languageCode])) {
                continue;
            }

            $languageCodes[$field->languageCode] = true;
        }

        return array_keys($languageCodes);
    }

    /**
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    protected function mapStructFieldsForUpdate($initialLanguageCode, $structFields, $mainLanguageCode, $fieldDefinitions)
    {
        $initialLanguageCode = $initialLanguageCode ?: $mainLanguageCode;

        $mappedFieldDefinitions = [];
        foreach ($fieldDefinitions as $fieldDefinition) {
            $mappedFieldDefinitions[$fieldDefinition->identifier] = $fieldDefinition;
        }

        $mappedStructFields = [];
        foreach ($structFields as $structField) {
            $identifier = $structField->fieldDefIdentifier;

            if ($structField->languageCode !== null) {
                $languageCode = $structField->languageCode;
            } elseif ($mappedFieldDefinitions[$identifier]->isTranslatable) {
                $languageCode = $initialLanguageCode;
            } else {
                $languageCode = $mainLanguageCode;
            }

            $mappedStructFields[$identifier][$languageCode] = (string)$structField->value;
        }

        return $mappedStructFields;
    }

    /**
     * Returns full, possibly redundant array of field values, indexed by field definition
     * identifier and language code.
     *
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param array $languageCodes
     *
     * @return array
     */
    protected function determineValuesForUpdate(
        $initialLanguageCode,
        array $structFields,
        Content $content,
        array $fieldDefinitions,
        array $languageCodes
    ) {
        $mainLanguageCode = $content->versionInfo->contentInfo->mainLanguageCode;

        $mappedStructFields = $this->mapStructFieldsForUpdate(
            $initialLanguageCode,
            $structFields,
            $mainLanguageCode,
            $fieldDefinitions
        );

        $values = [];

        foreach ($fieldDefinitions as $fieldDefinition) {
            $identifier = $fieldDefinition->identifier;
            foreach ($languageCodes as $languageCode) {
                if (!$fieldDefinition->isTranslatable) {
                    if (isset($mappedStructFields[$identifier][$mainLanguageCode])) {
                        $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$mainLanguageCode];
                    } else {
                        $values[$identifier][$languageCode] = (string)$content->fields[$identifier][$mainLanguageCode];
                    }
                    continue;
                }

                if (isset($mappedStructFields[$identifier][$languageCode])) {
                    $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$languageCode];
                    continue;
                }

                if (isset($content->fields[$identifier][$languageCode])) {
                    $values[$identifier][$languageCode] = (string)$content->fields[$identifier][$languageCode];
                    continue;
                }

                $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
            }
        }

        return $this->stubValues($values);
    }

    protected function stubValues(array $fieldValues)
    {
        foreach ($fieldValues as &$languageValues) {
            foreach ($languageValues as &$value) {
                $value = new ValueStub($value);
            }
        }

        return $fieldValues;
    }

    /**
     * Asserts that calling updateContent() with given API field set causes calling
     * Handler::updateContent() with given SPI field set.
     *
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $existingFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param bool $execute
     *
     * @return mixed
     */
    protected function assertForTestUpdateContentNonRedundantFieldSet(
        $initialLanguageCode,
        array $structFields,
        array $spiFields,
        array $existingFields,
        array $fieldDefinitions,
        $execute = true
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $permissionResolverMock = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent', 'loadRelations']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $existingLanguageCodes = array_map(
            function (Field $field) {
                return $field->languageCode;
            },
            $existingFields
        );
        $languageCodes = $this->determineLanguageCodesForUpdate(
            $initialLanguageCode,
            $structFields,
            $existingLanguageCodes
        );
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(
                    [
                        'id' => 42,
                        'contentTypeId' => 24,
                        'mainLanguageCode' => 'eng-GB',
                    ]
                ),
                'versionNo' => 7,
                'languageCodes' => $existingLanguageCodes,
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => $existingFields,
            ]
        );
        $contentType = new ContentType(['fieldDefinitions' => $fieldDefinitions]);

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $repositoryMock->expects($this->once())->method('beginTransaction');

        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(true));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(24))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $repositoryMock->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(169)));

        $fieldTypeMock->expects($this->any())
            ->method('acceptValue')
            ->will(
                $this->returnCallback(
                    function ($valueString) {
                        return new ValueStub($valueString);
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects($this->any())
            ->method('toPersistenceValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) {
                        return (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('isEmptyValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) use ($emptyValue) {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('validate')
            ->will($this->returnValue([]));

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        $relationProcessorMock
            ->expects($this->exactly(count($fieldDefinitions) * count($languageCodes)))
            ->method('appendFieldRelations')
            ->with(
                $this->isType('array'),
                $this->isType('array'),
                $this->isInstanceOf(SPIFieldType::class),
                $this->isInstanceOf(Value::class),
                $this->anything()
            );

        $values = $this->determineValuesForUpdate(
            $initialLanguageCode,
            $structFields,
            $content,
            $fieldDefinitions,
            $languageCodes
        );
        $nameSchemaServiceMock->expects($this->once())
            ->method('resolveNameSchema')
            ->with(
                $this->equalTo($content),
                $this->equalTo($values),
                $this->equalTo($languageCodes)
            )->will($this->returnValue([]));

        $existingRelations = ['RELATIONS!!!'];
        $mockedService->expects($this->once())
            ->method('loadRelations')
            ->with($content->versionInfo)
            ->will($this->returnValue($existingRelations));
        $relationProcessorMock->expects($this->any())
            ->method('processFieldRelations')
            ->with(
                $this->isType('array'),
                $this->equalTo(42),
                $this->isType('int'),
                $this->equalTo($contentType),
                $this->equalTo($existingRelations)
            );

        $contentUpdateStruct = new ContentUpdateStruct(
            [
                'fields' => $structFields,
                'initialLanguageCode' => $initialLanguageCode,
            ]
        );

        if ($execute) {
            $spiContentUpdateStruct = new SPIContentUpdateStruct(
                [
                    'creatorId' => 169,
                    'fields' => $spiFields,
                    'modificationDate' => time(),
                    'initialLanguageId' => 4242,
                ]
            );

            // During code coverage runs, timestamp might differ 1-3 seconds
            $spiContentUpdateStructTs1 = clone $spiContentUpdateStruct;
            ++$spiContentUpdateStructTs1->modificationDate;

            $spiContentUpdateStructTs2 = clone $spiContentUpdateStructTs1;
            ++$spiContentUpdateStructTs2->modificationDate;

            $spiContentUpdateStructTs3 = clone $spiContentUpdateStructTs2;
            ++$spiContentUpdateStructTs3->modificationDate;

            $spiContent = new SPIContent(
                [
                    'versionInfo' => new SPIContent\VersionInfo(
                        [
                            'contentInfo' => new SPIContent\ContentInfo(['id' => 42]),
                            'versionNo' => 7,
                        ]
                    ),
                ]
            );

            $contentHandlerMock->expects($this->once())
                ->method('updateContent')
                ->with(
                    42,
                    7,
                    $this->logicalOr($spiContentUpdateStruct, $spiContentUpdateStructTs1, $spiContentUpdateStructTs2, $spiContentUpdateStructTs3)
                )
                ->will($this->returnValue($spiContent));

            $repositoryMock->expects($this->once())->method('commit');
            $domainMapperMock->expects($this->once())
                ->method('buildContentDomainObject')
                ->with(
                    $this->isInstanceOf(SPIContent::class),
                    $this->isInstanceOf(APIContentType::class)
                );

            $mockedService->updateContent($content->versionInfo, $contentUpdateStruct);
        }

        return [$content->versionInfo, $contentUpdateStruct];
    }

    public function providerForTestUpdateContentNonRedundantFieldSet1()
    {
        $spiFields = [
            new SPIField(
                [
                    'id' => '100',
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
        ];

        return [
            // With languages set
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields,
            ],
            // Without languages set
            [
                null,
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields,
            ],
            // Adding new language without fields
            [
                'eng-US',
                [],
                [],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing the simplest use case.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet1
     */
    public function testUpdateContentNonRedundantFieldSet1($initialLanguageCode, $structFields, $spiFields)
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier',
                    'value' => 'initialValue',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet2()
    {
        $spiFields0 = [
            new SPIField(
                [
                    'id' => '100',
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields1 = [
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields2 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];

        return [
            // 0. With languages set
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 1. Without languages set
            [
                null,
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 2. New language with language set
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 3. New language without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 4. New language and existing language with language set
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 5. New language and existing language without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 6. Adding new language without fields
            [
                'eng-US',
                [],
                [
                    new SPIField(
                        [
                            'id' => null,
                            'fieldDefinitionId' => 'fieldDefinitionId',
                            'type' => 'fieldTypeIdentifier',
                            'value' => 'defaultValue',
                            'languageCode' => 'eng-US',
                            'versionNo' => 7,
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with translatable field.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet2
     */
    public function testUpdateContentNonRedundantFieldSet2($initialLanguageCode, $structFields, $spiFields)
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier',
                    'value' => 'initialValue',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet3()
    {
        $spiFields0 = [
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields1 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields2 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => 101,
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue3',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields3 = [
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];

        return [
            // 0. ew language with language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 1. New language without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 2. New language and existing language with language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 3. New language and existing language without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 4. New language and existing language with untranslatable field, with language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue3',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 5. New language and existing language with untranslatable field, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue3',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 6. Adding new language without fields
            [
                'eng-US',
                [],
                $spiFields3,
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with new language and untranslatable field.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet3
     */
    public function testUpdateContentNonRedundantFieldSet3($initialLanguageCode, $structFields, $spiFields)
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier1',
                    'value' => 'initialValue1',
                    'languageCode' => 'eng-GB',
                ]
            ),
            new Field(
                [
                    'id' => '101',
                    'fieldDefIdentifier' => 'identifier2',
                    'value' => 'initialValue2',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier1',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue1',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId2',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier2',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue2',
                ]
            ),
        ];

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet4()
    {
        $spiFields0 = [
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields1 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => self::EMPTY_FIELD_VALUE,
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields2 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => self::EMPTY_FIELD_VALUE,
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
        ];

        return [
            // 0. New translation with empty field by default
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 1. New translation with empty field by default, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 2. New translation with empty field given
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 3. New translation with empty field given, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 4. Updating existing language with empty value
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 5. Updating existing language with empty value, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 6. Updating existing language with empty value and adding new language with empty value
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 7. Updating existing language with empty value and adding new language with empty value,
            // without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 8. Adding new language with no fields given
            [
                'eng-US',
                [],
                [],
            ],
            // 9. Adding new language with fields
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
                [],
            ],
            // 10. Adding new language with fields, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null,
                        ]
                    ),
                ],
                [],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with empty values.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet4
     */
    public function testUpdateContentNonRedundantFieldSet4($initialLanguageCode, $structFields, $spiFields)
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier1',
                    'value' => 'initialValue1',
                    'languageCode' => 'eng-GB',
                ]
            ),
            new Field(
                [
                    'id' => '101',
                    'fieldDefIdentifier' => 'identifier2',
                    'value' => 'initialValue2',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier1',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId2',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier2',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
        ];

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    /**
     * @todo add first field empty
     *
     * @return array
     */
    public function providerForTestUpdateContentNonRedundantFieldSetComplex()
    {
        $spiFields0 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1-eng-GB',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue4',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields1 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1-eng-GB',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue4',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];
        $spiFields2 = [
            new SPIField(
                [
                    'id' => 100,
                    'fieldDefinitionId' => 'fieldDefinitionId1',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue1-eng-GB',
                    'languageCode' => 'eng-GB',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId2',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'newValue2',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue4',
                    'languageCode' => 'ger-DE',
                    'versionNo' => 7,
                ]
            ),
            new SPIField(
                [
                    'id' => null,
                    'fieldDefinitionId' => 'fieldDefinitionId4',
                    'type' => 'fieldTypeIdentifier',
                    'value' => 'defaultValue4',
                    'languageCode' => 'eng-US',
                    'versionNo' => 7,
                ]
            ),
        ];

        return [
            // 0. Add new language and update existing
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier4',
                            'value' => 'newValue4',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 1. Add new language and update existing, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier4',
                            'value' => 'newValue4',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields0,
            ],
            // 2. Add new language and update existing variant
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 3. Add new language and update existing variant, without language set
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => null,
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields1,
            ],
            // 4. Update with multiple languages
            [
                'ger-DE',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
                $spiFields2,
            ],
            // 5. Update with multiple languages without language set
            [
                'ger-DE',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier2',
                            'value' => 'newValue2',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier1',
                            'value' => 'newValue1-eng-GB',
                            'languageCode' => null,
                        ]
                    ),
                ],
                $spiFields2,
            ],
        ];
    }

    protected function fixturesForTestUpdateContentNonRedundantFieldSetComplex()
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier1',
                    'value' => 'initialValue1',
                    'languageCode' => 'eng-GB',
                ]
            ),
            new Field(
                [
                    'id' => '101',
                    'fieldDefIdentifier' => 'identifier2',
                    'value' => 'initialValue2',
                    'languageCode' => 'eng-GB',
                ]
            ),
            new Field(
                [
                    'id' => '102',
                    'fieldDefIdentifier' => 'identifier3',
                    'value' => 'initialValue3',
                    'languageCode' => 'eng-GB',
                ]
            ),
            new Field(
                [
                    'id' => '103',
                    'fieldDefIdentifier' => 'identifier4',
                    'value' => 'initialValue4',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier1',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId2',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier2',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId3',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier3',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue3',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId4',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier4',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue4',
                ]
            ),
        ];

        return [$existingFields, $fieldDefinitions];
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing more complex cases.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSetComplex
     */
    public function testUpdateContentNonRedundantFieldSetComplex($initialLanguageCode, $structFields, $spiFields)
    {
        list($existingFields, $fieldDefinitions) = $this->fixturesForTestUpdateContentNonRedundantFieldSetComplex();

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentWithInvalidLanguage()
    {
        return [
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'Klingon',
                        ]
                    ),
                ],
            ],
            [
                'Klingon',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentWithInvalidLanguage
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'Language' with identifier 'Klingon'
     */
    public function testUpdateContentWithInvalidLanguage($initialLanguageCode, $structFields)
    {
        $repositoryMock = $this->getRepositoryMock();
        $permissionResolverMock = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(
                    [
                        'id' => 42,
                        'contentTypeId' => 24,
                        'mainLanguageCode' => 'eng-GB',
                    ]
                ),
                'versionNo' => 7,
                'languageCodes' => ['eng-GB'],
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => [],
            ]
        );

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function ($languageCode) {
                        if ($languageCode === 'Klingon') {
                            throw new NotFoundException('Language', 'Klingon');
                        }

                        return new Language(['id' => 4242]);
                    }
                )
            );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(true));

        $contentUpdateStruct = new ContentUpdateStruct(
            [
                'fields' => $structFields,
                'initialLanguageCode' => $initialLanguageCode,
            ]
        );

        $mockedService->updateContent($content->versionInfo, $contentUpdateStruct);
    }

    protected function assertForUpdateContentContentValidationException(
        $initialLanguageCode,
        $structFields,
        $fieldDefinitions = []
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $permissionResolverMock = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(
                    [
                        'id' => 42,
                        'contentTypeId' => 24,
                        'mainLanguageCode' => 'eng-GB',
                    ]
                ),
                'versionNo' => 7,
                'languageCodes' => ['eng-GB'],
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => [],
            ]
        );
        $contentType = new ContentType(['fieldDefinitions' => $fieldDefinitions]);

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function ($languageCode) {
                        if ($languageCode === 'Klingon') {
                            throw new NotFoundException('Language', 'Klingon');
                        }

                        return new Language(['id' => 4242]);
                    }
                )
            );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(true));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(24))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $contentUpdateStruct = new ContentUpdateStruct(
            [
                'fields' => $structFields,
                'initialLanguageCode' => $initialLanguageCode,
            ]
        );

        $mockedService->updateContent($content->versionInfo, $contentUpdateStruct);
    }

    public function providerForTestUpdateContentThrowsContentValidationExceptionFieldDefinition()
    {
        return [
            [
                'eng-GB',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-GB',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentValidationExceptionFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage Field definition 'identifier' does not exist in given ContentType
     */
    public function testUpdateContentThrowsContentValidationExceptionFieldDefinition($initialLanguageCode, $structFields)
    {
        $this->assertForUpdateContentContentValidationException(
            $initialLanguageCode,
            $structFields,
            []
        );
    }

    public function providerForTestUpdateContentThrowsContentValidationExceptionTranslation()
    {
        return [
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => 'newValue',
                            'languageCode' => 'eng-US',
                        ]
                    ),
                ],
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentValidationExceptionTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage A value is set for non translatable field definition 'identifier' with language 'eng-US'
     */
    public function testUpdateContentThrowsContentValidationExceptionTranslation($initialLanguageCode, $structFields)
    {
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId1',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => self::EMPTY_FIELD_VALUE,
                ]
            ),
        ];

        $this->assertForUpdateContentContentValidationException(
            $initialLanguageCode,
            $structFields,
            $fieldDefinitions
        );
    }

    public function assertForTestUpdateContentRequiredField(
        $initialLanguageCode,
        $structFields,
        $existingFields,
        $fieldDefinitions
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $permissionResolver = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $existingLanguageCodes = array_map(
            function (Field $field) {
                return $field->languageCode;
            },
            $existingFields
        );
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(
                    [
                        'id' => 42,
                        'contentTypeId' => 24,
                        'mainLanguageCode' => 'eng-GB',
                    ]
                ),
                'versionNo' => 7,
                'languageCodes' => $existingLanguageCodes,
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => $existingFields,
            ]
        );
        $contentType = new ContentType(['fieldDefinitions' => $fieldDefinitions]);

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $permissionResolver->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(true));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(24))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $fieldTypeMock->expects($this->any())
            ->method('acceptValue')
            ->will(
                $this->returnCallback(
                    function ($valueString) {
                        return new ValueStub($valueString);
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects($this->any())
            ->method('isEmptyValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) use ($emptyValue) {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects($this->any())
            ->method('validate')
            ->with(
                $this->isInstanceOf(APIFieldDefinition::class),
                $this->isInstanceOf(Value::class)
            );

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        $contentUpdateStruct = new ContentUpdateStruct(
            [
                'fields' => $structFields,
                'initialLanguageCode' => $initialLanguageCode,
            ]
        );

        return [$content->versionInfo, $contentUpdateStruct];
    }

    public function providerForTestUpdateContentRequiredField()
    {
        return [
            [
                'eng-US',
                [
                    new Field(
                        [
                            'fieldDefIdentifier' => 'identifier',
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null,
                        ]
                    ),
                ],
                'identifier',
                'eng-US',
            ],
        ];
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentRequiredField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateContentRequiredField(
        $initialLanguageCode,
        $structFields,
        $identifier,
        $languageCode
    ) {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier',
                    'value' => 'initialValue',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];
        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => true,
                    'identifier' => 'identifier',
                    'isRequired' => true,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];
        list($versionInfo, $contentUpdateStruct) =
            $this->assertForTestUpdateContentRequiredField(
                $initialLanguageCode,
                $structFields,
                $existingFields,
                $fieldDefinitions
            );

        try {
            $this->partlyMockedContentService->updateContent($versionInfo, $contentUpdateStruct);
        } catch (ContentValidationException $e) {
            $this->assertEquals(
                "Value for required field definition '{$identifier}' with language '{$languageCode}' is empty",
                $e->getMessage()
            );

            throw $e;
        }
    }

    public function assertForTestUpdateContentThrowsContentFieldValidationException(
        $initialLanguageCode,
        $structFields,
        $existingFields,
        $fieldDefinitions
    ) {
        $repositoryMock = $this->getRepositoryMock();
        $permissionResolverMock = $this->getPermissionResolverMock();
        $mockedService = $this->getPartlyMockedContentService(['loadContent']);
        /** @var \PHPUnit\Framework\MockObject\MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeMock = $this->createMock(SPIFieldType::class);
        $existingLanguageCodes = array_map(
            function (Field $field) {
                return $field->languageCode;
            },
            $existingFields
        );
        $languageCodes = $this->determineLanguageCodesForUpdate(
            $initialLanguageCode,
            $structFields,
            $existingLanguageCodes
        );
        $versionInfo = new VersionInfo(
            [
                'contentInfo' => new ContentInfo(
                    [
                        'id' => 42,
                        'contentTypeId' => 24,
                        'mainLanguageCode' => 'eng-GB',
                    ]
                ),
                'versionNo' => 7,
                'languageCodes' => $existingLanguageCodes,
                'status' => VersionInfo::STATUS_DRAFT,
            ]
        );
        $content = new Content(
            [
                'versionInfo' => $versionInfo,
                'internalFields' => $existingFields,
            ]
        );
        $contentType = new ContentType(['fieldDefinitions' => $fieldDefinitions]);

        $languageHandlerMock->expects($this->any())
            ->method('loadByLanguageCode')
            ->with($this->isType('string'))
            ->will(
                $this->returnCallback(
                    function () {
                        return new Language(['id' => 4242]);
                    }
                )
            );

        $mockedService->expects($this->once())
            ->method('loadContent')
            ->with(
                $this->equalTo(42),
                $this->equalTo(null),
                $this->equalTo(7)
            )->will(
                $this->returnValue($content)
            );

        $permissionResolverMock->expects($this->once())
            ->method('canUser')
            ->with(
                $this->equalTo('content'),
                $this->equalTo('edit'),
                $this->equalTo($content),
                $this->isType('array')
            )->will($this->returnValue(true));

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo(24))
            ->will($this->returnValue($contentType));

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        $fieldValues = $this->determineValuesForUpdate(
            $initialLanguageCode,
            $structFields,
            $content,
            $fieldDefinitions,
            $languageCodes
        );
        $allFieldErrors = [];
        $emptyValue = self::EMPTY_FIELD_VALUE;

        $fieldTypeMock->expects($this->exactly(count($fieldValues) * count($languageCodes)))
            ->method('acceptValue')
            ->will(
                $this->returnCallback(
                    function ($valueString) {
                        return new ValueStub($valueString);
                    }
                )
            );

        $fieldTypeMock->expects($this->exactly(count($fieldValues) * count($languageCodes)))
            ->method('isEmptyValue')
            ->will(
                $this->returnCallback(
                    function (ValueStub $value) use ($emptyValue) {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock
            ->expects($this->any())
            ->method('validate')
            ->willReturnArgument(1);

        $this->getFieldTypeRegistryMock()->expects($this->any())
            ->method('getFieldType')
            ->will($this->returnValue($fieldTypeMock));

        $contentUpdateStruct = new ContentUpdateStruct(
            [
                'fields' => $structFields,
                'initialLanguageCode' => $initialLanguageCode,
            ]
        );

        return [$content->versionInfo, $contentUpdateStruct, $allFieldErrors];
    }

    public function providerForTestUpdateContentThrowsContentFieldValidationException()
    {
        $allFieldErrors = [
            [
                'fieldDefinitionId1' => [
                    'eng-GB' => 'newValue1-eng-GB',
                    'eng-US' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-GB' => 'initialValue2',
                ],
                'fieldDefinitionId3' => [
                    'eng-GB' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'eng-GB' => 'initialValue4',
                    'eng-US' => 'newValue4',
                ],
            ],
            [
                'fieldDefinitionId1' => [
                    'eng-GB' => 'newValue1-eng-GB',
                    'eng-US' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-GB' => 'initialValue2',
                ],
                'fieldDefinitionId3' => [
                    'eng-GB' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'eng-GB' => 'initialValue4',
                    'eng-US' => 'newValue4',
                ],
            ],
            [
                'fieldDefinitionId1' => [
                    'eng-GB' => 'newValue1-eng-GB',
                    'eng-US' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-GB' => 'initialValue2',
                    'eng-US' => 'newValue2',
                ],
                'fieldDefinitionId3' => [
                    'eng-GB' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'eng-GB' => 'initialValue4',
                    'eng-US' => 'defaultValue4',
                ],
            ],
            [
                'fieldDefinitionId1' => [
                    'eng-GB' => 'newValue1-eng-GB',
                    'eng-US' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-GB' => 'initialValue2',
                    'eng-US' => 'newValue2',
                ],
                'fieldDefinitionId3' => [
                    'eng-GB' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'eng-GB' => 'initialValue4',
                    'eng-US' => 'defaultValue4',
                ],
            ],
            [
                'fieldDefinitionId1' => [
                    'eng-GB' => 'newValue1-eng-GB',
                    'ger-DE' => 'newValue1-eng-GB',
                    'eng-US' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-GB' => 'initialValue2',
                    'eng-US' => 'newValue2',
                ],
                'fieldDefinitionId3' => [
                    'eng-GB' => 'initialValue3',
                    'ger-DE' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'eng-GB' => 'initialValue4',
                    'eng-US' => 'defaultValue4',
                    'ger-DE' => 'defaultValue4',
                ],
            ],
            [
                'fieldDefinitionId1' => [
                    'eng-US' => 'newValue1-eng-GB',
                    'ger-DE' => 'newValue1-eng-GB',
                ],
                'fieldDefinitionId2' => [
                    'eng-US' => 'newValue2',
                ],
                'fieldDefinitionId3' => [
                    'ger-DE' => 'initialValue3',
                    'eng-US' => 'initialValue3',
                ],
                'fieldDefinitionId4' => [
                    'ger-DE' => 'defaultValue4',
                    'eng-US' => 'defaultValue4',
                ],
            ],
        ];

        $data = $this->providerForTestUpdateContentNonRedundantFieldSetComplex();
        $count = count($data);
        for ($i = 0; $i < $count; ++$i) {
            $data[$i][] = $allFieldErrors[$i];
        }

        return $data;
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @expectedExceptionMessage Content fields did not validate
     */
    public function testUpdateContentThrowsContentFieldValidationException($initialLanguageCode, $structFields, $spiField, $allFieldErrors)
    {
        list($existingFields, $fieldDefinitions) = $this->fixturesForTestUpdateContentNonRedundantFieldSetComplex();
        list($versionInfo, $contentUpdateStruct) =
            $this->assertForTestUpdateContentThrowsContentFieldValidationException(
                $initialLanguageCode,
                $structFields,
                $existingFields,
                $fieldDefinitions
            );

        try {
            $this->partlyMockedContentService->updateContent($versionInfo, $contentUpdateStruct);
        } catch (ContentFieldValidationException $e) {
            $this->assertEquals($allFieldErrors, $e->getFieldErrors());
            throw $e;
        }
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \Exception
     * @expectedExceptionMessage Store failed
     */
    public function testUpdateContentTransactionRollback()
    {
        $existingFields = [
            new Field(
                [
                    'id' => '100',
                    'fieldDefIdentifier' => 'identifier',
                    'value' => 'initialValue',
                    'languageCode' => 'eng-GB',
                ]
            ),
        ];

        $fieldDefinitions = [
            new FieldDefinition(
                [
                    'id' => 'fieldDefinitionId',
                    'fieldTypeIdentifier' => 'fieldTypeIdentifier',
                    'isTranslatable' => false,
                    'identifier' => 'identifier',
                    'isRequired' => false,
                    'defaultValue' => 'defaultValue',
                ]
            ),
        ];

        // Setup a simple case that will pass
        list($versionInfo, $contentUpdateStruct) = $this->assertForTestUpdateContentNonRedundantFieldSet(
            'eng-US',
            [],
            [],
            $existingFields,
            $fieldDefinitions,
            // Do not execute test
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects($this->never())->method('commit');
        $repositoryMock->expects($this->once())->method('rollback');

        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $contentHandlerMock->expects($this->once())
            ->method('updateContent')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything()
            )->will($this->throwException(new \Exception('Store failed')));

        // Execute
        $this->partlyMockedContentService->updateContent($versionInfo, $contentUpdateStruct);
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService(['internalLoadContentInfo']);
        $contentInfo = $this->createMock(APIContentInfo::class);
        $locationCreateStruct = new LocationCreateStruct();
        $location = new Location(['id' => $locationCreateStruct->parentLocationId]);
        $locationServiceMock = $this->getLocationServiceMock();

        $repository->expects($this->once())
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock))
        ;

        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with(
                $locationCreateStruct->parentLocationId
            )
            ->will($this->returnValue($location))
        ;

        $contentInfo->expects($this->any())
            ->method('__get')
            ->with('sectionId')
            ->will($this->returnValue(42));

        $repository->expects($this->once())
            ->method('canUser')
            ->with(
                'content',
                'create',
                $contentInfo,
                [$location]
            )
            ->will($this->returnValue(false));

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->copyContent($contentInfo, $locationCreateStruct);
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::internalPublishVersion
     */
    public function testCopyContent()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService([
            'internalLoadContentInfo',
            'internalLoadContent',
            'getUnixTimestamp',
        ]);
        $locationServiceMock = $this->getLocationServiceMock();
        $contentInfoMock = $this->createMock(APIContentInfo::class);
        $locationCreateStruct = new LocationCreateStruct();
        $location = new Location(['id' => $locationCreateStruct->parentLocationId]);
        $user = $this->getStubbedUser(14);

        $permissionResolverMock = $this->getPermissionResolverMock();

        $permissionResolverMock
            ->method('getCurrentUserReference')
            ->willReturn($user);

        $repositoryMock
            ->method('getPermissionResolver')
            ->willReturn($permissionResolverMock);

        $repositoryMock->expects($this->exactly(3))
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock));

        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with($locationCreateStruct->parentLocationId)
            ->will($this->returnValue($location))
        ;

        $contentInfoMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['versionNo', 123],
                    ]
                )
            );

        $versionInfoMock->expects($this->once())
            ->method('isDraft')
            ->willReturn(true);

        $versionInfoMock->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue($contentInfoMock));

        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('commit');
        $repositoryMock
            ->method('canUser')
            ->willReturnMap(
                [
                    ['content', 'create', $contentInfoMock, [$location], true],
                    ['content', 'manage_locations', $contentInfoMock, [$location], true],
                ]
            );

        $spiContentInfo = new SPIContentInfo(['id' => 42]);
        $spiVersionInfo = new SPIVersionInfo(
            [
                'contentInfo' => $spiContentInfo,
                'creationDate' => 123456,
            ]
        );
        $spiContent = new SPIContent(['versionInfo' => $spiVersionInfo]);
        $contentHandlerMock->expects($this->once())
            ->method('copy')
            ->with(42, null)
            ->will($this->returnValue($spiContent));

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with($spiVersionInfo)
            ->will($this->returnValue($versionInfoMock));

        /* @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfoMock */
        $content = $this->mockPublishVersion(123456, 126666);
        $locationServiceMock->expects($this->once())
            ->method('createLocation')
            ->with(
                $content->getVersionInfo()->getContentInfo(),
                $locationCreateStruct
            );

        $contentService->expects($this->once())
            ->method('internalLoadContent')
            ->with(
                $content->id
            )
            ->will($this->returnValue($content));

        $contentService->expects($this->once())
            ->method('getUnixTimestamp')
            ->will($this->returnValue(126666));

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent($contentInfoMock, $locationCreateStruct, null);
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::internalPublishVersion
     */
    public function testCopyContentWithVersionInfo()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService([
            'internalLoadContentInfo',
            'internalLoadContent',
            'getUnixTimestamp',
        ]);
        $locationServiceMock = $this->getLocationServiceMock();
        $contentInfoMock = $this->createMock(APIContentInfo::class);
        $locationCreateStruct = new LocationCreateStruct();
        $location = new Location(['id' => $locationCreateStruct->parentLocationId]);
        $user = $this->getStubbedUser(14);

        $permissionResolverMock = $this->getPermissionResolverMock();

        $permissionResolverMock
            ->method('getCurrentUserReference')
            ->willReturn($user);

        $repositoryMock
            ->method('getPermissionResolver')
            ->willReturn($permissionResolverMock);

        $repositoryMock->expects($this->exactly(3))
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock));

        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with($locationCreateStruct->parentLocationId)
            ->will($this->returnValue($location))
        ;

        $contentInfoMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));
        $versionInfoMock = $this->createMock(APIVersionInfo::class);

        $versionInfoMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['versionNo', 123],
                    ]
                )
            );
        $versionInfoMock->expects($this->once())
            ->method('isDraft')
            ->willReturn(true);
        $versionInfoMock->expects($this->once())
            ->method('getContentInfo')
            ->will($this->returnValue($contentInfoMock));

        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('commit');
        $repositoryMock
            ->method('canUser')
            ->willReturnMap(
                [
                    ['content', 'create', $contentInfoMock, [$location], true],
                    ['content', 'manage_locations', $contentInfoMock, [$location], true],
                ]
            );

        $spiContentInfo = new SPIContentInfo(['id' => 42]);
        $spiVersionInfo = new SPIVersionInfo(
            [
                'contentInfo' => $spiContentInfo,
                'creationDate' => 123456,
            ]
        );
        $spiContent = new SPIContent(['versionInfo' => $spiVersionInfo]);
        $contentHandlerMock->expects($this->once())
            ->method('copy')
            ->with(42, 123)
            ->will($this->returnValue($spiContent));

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $domainMapperMock->expects($this->once())
            ->method('buildVersionInfoDomainObject')
            ->with($spiVersionInfo)
            ->will($this->returnValue($versionInfoMock));

        /* @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfoMock */
        $content = $this->mockPublishVersion(123456, 126666);
        $locationServiceMock->expects($this->once())
            ->method('createLocation')
            ->with(
                $content->getVersionInfo()->getContentInfo(),
                $locationCreateStruct
            );

        $contentService->expects($this->once())
            ->method('internalLoadContent')
            ->with(
                $content->id
            )
            ->will($this->returnValue($content));

        $contentService->expects($this->once())
            ->method('getUnixTimestamp')
            ->will($this->returnValue(126666));

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent($contentInfoMock, $locationCreateStruct, $versionInfoMock);
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\ContentService::internalPublishVersion
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testCopyContentWithRollback()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $locationCreateStruct = new LocationCreateStruct();
        $location = new Location(['id' => $locationCreateStruct->parentLocationId]);
        $locationServiceMock = $this->getLocationServiceMock();
        $user = $this->getStubbedUser(14);

        $permissionResolverMock = $this->getPermissionResolverMock();

        $permissionResolverMock
            ->method('getCurrentUserReference')
            ->willReturn($user);

        $repositoryMock
            ->method('getPermissionResolver')
            ->willReturn($permissionResolverMock);

        $repositoryMock->expects($this->once())
            ->method('getLocationService')
            ->will($this->returnValue($locationServiceMock))
        ;

        $locationServiceMock->expects($this->once())
            ->method('loadLocation')
            ->with($locationCreateStruct->parentLocationId)
            ->will($this->returnValue($location))
        ;

        $contentInfoMock = $this->createMock(APIContentInfo::class);
        $contentInfoMock->expects($this->any())
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(42));

        $this->mockGetDefaultObjectStates();

        $repositoryMock->expects($this->once())->method('beginTransaction');
        $repositoryMock->expects($this->once())->method('rollback');
        $repositoryMock
            ->method('canUser')
            ->willReturnMap(
                [
                    ['content', 'create', $contentInfoMock, [$location], true],
                    ['content', 'manage_locations', $contentInfoMock, [$location], true],
                ]
            );

        $contentHandlerMock->expects($this->once())
            ->method('copy')
            ->with(42, null)
            ->will($this->throwException(new Exception('Handler threw an exception')));

        /* @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent($contentInfoMock, $locationCreateStruct, null);
    }

    /**
     * Reusable method for setting exceptions on buildContentDomainObject usage.
     *
     * Plain usage as in when content type is loaded directly.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param array $translations
     * @param bool $useAlwaysAvailable
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\Values\Content\Content
     */
    private function mockBuildContentDomainObject(SPIContent $spiContent, array $translations = null, bool $useAlwaysAvailable = null)
    {
        $contentTypeId = $spiContent->versionInfo->contentInfo->contentTypeId;
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $repositoryMock = $this->getRepositoryMock();

        $contentType = new ContentType([
            'id' => $contentTypeId,
            'fieldDefinitions' => [],
        ]);

        $repositoryMock->expects($this->once())
            ->method('getContentTypeService')
            ->willReturn($contentTypeServiceMock);

        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with($this->equalTo($contentTypeId))
            ->willReturn($contentType);

        $content = $this->createMock(APIContent::class);

        $this->getDomainMapperMock()
            ->expects($this->once())
            ->method('buildContentDomainObject')
            ->with($spiContent, $contentType, $translations ?? [], $useAlwaysAvailable)
            ->willReturn($content);

        return $content;
    }

    protected function mockGetDefaultObjectStates()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();

        $objectStateGroups = [
            new SPIObjectStateGroup(['id' => 10]),
            new SPIObjectStateGroup(['id' => 20]),
        ];

        /* @var \PHPUnit\Framework\MockObject\MockObject $objectStateHandlerMock */
        $objectStateHandlerMock->expects($this->once())
            ->method('loadAllGroups')
            ->will($this->returnValue($objectStateGroups));

        $objectStateHandlerMock->expects($this->at(1))
            ->method('loadObjectStates')
            ->with($this->equalTo(10))
            ->will(
                $this->returnValue(
                    [
                        new SPIObjectState(['id' => 11, 'groupId' => 10]),
                        new SPIObjectState(['id' => 12, 'groupId' => 10]),
                    ]
                )
            );

        $objectStateHandlerMock->expects($this->at(2))
            ->method('loadObjectStates')
            ->with($this->equalTo(20))
            ->will(
                $this->returnValue(
                    [
                        new SPIObjectState(['id' => 21, 'groupId' => 20]),
                        new SPIObjectState(['id' => 22, 'groupId' => 20]),
                    ]
                )
            );
    }

    protected function mockSetDefaultObjectStates()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();

        $defaultObjectStates = [
            new SPIObjectState(['id' => 11, 'groupId' => 10]),
            new SPIObjectState(['id' => 21, 'groupId' => 20]),
        ];
        foreach ($defaultObjectStates as $index => $objectState) {
            $objectStateHandlerMock->expects($this->at($index + 3))
                ->method('setContentState')
                ->with(
                    42,
                    $objectState->groupId,
                    $objectState->id
                );
        }
    }

    /**
     * @param int|null $publicationDate
     * @param int|null $modificationDate
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function mockPublishVersion($publicationDate = null, $modificationDate = null)
    {
        $versionInfoMock = $this->createMock(APIVersionInfo::class);
        $contentInfoMock = $this->createMock(APIContentInfo::class);
        /* @var \PHPUnit\Framework\MockObject\MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $metadataUpdateStruct = new SPIMetadataUpdateStruct();

        $spiContent = new SPIContent([
            'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo(['id' => 42, 'contentTypeId' => 123]),
            ]),
        ]);

        $contentMock = $this->mockBuildContentDomainObject($spiContent);
        $contentMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', 42],
                        ['contentInfo', $contentInfoMock],
                        ['versionInfo', $versionInfoMock],
                    ]
                )
            );
        $contentMock->expects($this->any())
            ->method('getVersionInfo')
            ->will($this->returnValue($versionInfoMock));
        $versionInfoMock->expects($this->any())
            ->method('getContentInfo')
            ->will($this->returnValue($contentInfoMock));
        $versionInfoMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['languageCodes', ['eng-GB']],
                    ]
                )
            );
        $contentInfoMock->expects($this->any())
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['alwaysAvailable', true],
                        ['mainLanguageCode', 'eng-GB'],
                    ]
                )
            );

        $currentTime = time();
        if ($publicationDate === null && $versionInfoMock->versionNo === 1) {
            $publicationDate = $currentTime;
        }

        // Account for 1 second of test execution time
        $metadataUpdateStruct->publicationDate = $publicationDate;
        $metadataUpdateStruct->modificationDate = $modificationDate ?? $currentTime;

        $contentHandlerMock->expects($this->once())
            ->method('publish')
            ->with(
                42,
                123,
                $metadataUpdateStruct
            )
            ->will($this->returnValue($spiContent));

        /* @var \eZ\Publish\API\Repository\Values\Content\Content $contentMock */
        $this->mockPublishUrlAliasesForContent($contentMock);

        return $contentMock;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    protected function mockPublishUrlAliasesForContent(APIContent $content)
    {
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();
        $locationServiceMock = $this->getLocationServiceMock();
        $location = $this->createMock(APILocation::class);

        $location->expects($this->at(0))
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(123));
        $location->expects($this->at(1))
            ->method('__get')
            ->with('parentLocationId')
            ->will($this->returnValue(456));

        $urlAliasNames = ['eng-GB' => 'hello'];
        $nameSchemaServiceMock->expects($this->once())
            ->method('resolveUrlAliasSchema')
            ->with($content)
            ->will($this->returnValue($urlAliasNames));

        $locationServiceMock->expects($this->once())
            ->method('loadLocations')
            ->with($content->getVersionInfo()->getContentInfo())
            ->will($this->returnValue([$location]));

        $urlAliasHandlerMock->expects($this->once())
            ->method('publishUrlAliasForLocation')
            ->with(123, 456, 'hello', 'eng-GB', true, true);

        $location->expects($this->at(2))
            ->method('__get')
            ->with('id')
            ->will($this->returnValue(123));

        $location->expects($this->at(3))
            ->method('__get')
            ->with('parentLocationId')
            ->will($this->returnValue(456));

        $urlAliasHandlerMock->expects($this->once())
            ->method('archiveUrlAliasesForDeletedTranslations')
            ->with(123, 456, ['eng-GB']);
    }

    protected $domainMapperMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected function getDomainMapperMock()
    {
        if (!isset($this->domainMapperMock)) {
            $this->domainMapperMock = $this->createMock(DomainMapper::class);
        }

        return $this->domainMapperMock;
    }

    protected $relationProcessorMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Repository\Helper\RelationProcessor
     */
    protected function getRelationProcessorMock()
    {
        if (!isset($this->relationProcessorMock)) {
            $this->relationProcessorMock = $this->createMock(RelationProcessor::class);
        }

        return $this->relationProcessorMock;
    }

    protected $nameSchemaServiceMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Repository\Helper\NameSchemaService
     */
    protected function getNameSchemaServiceMock()
    {
        if (!isset($this->nameSchemaServiceMock)) {
            $this->nameSchemaServiceMock = $this->createMock(NameSchemaService::class);
        }

        return $this->nameSchemaServiceMock;
    }

    protected $contentTypeServiceMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\ContentTypeService
     */
    protected function getContentTypeServiceMock()
    {
        if (!isset($this->contentTypeServiceMock)) {
            $this->contentTypeServiceMock = $this->createMock(APIContentTypeService::class);
        }

        return $this->contentTypeServiceMock;
    }

    protected $locationServiceMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\LocationService
     */
    protected function getLocationServiceMock()
    {
        if (!isset($this->locationServiceMock)) {
            $this->locationServiceMock = $this->createMock(APILocationService::class);
        }

        return $this->locationServiceMock;
    }

    /** @var \eZ\Publish\Core\Repository\ContentService */
    protected $partlyMockedContentService;

    /**
     * Returns the content service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedContentService(array $methods = null)
    {
        if (!isset($this->partlyMockedContentService)) {
            $this->partlyMockedContentService = $this->getMockBuilder(ContentService::class)
                ->setMethods($methods)
                ->setConstructorArgs(
                    [
                        $this->getRepositoryMock(),
                        $this->getPersistenceMock(),
                        $this->getDomainMapperMock(),
                        $this->getRelationProcessorMock(),
                        $this->getNameSchemaServiceMock(),
                        $this->getFieldTypeRegistryMock(),
                        [],
                    ]
                )
                ->getMock();
        }

        return $this->partlyMockedContentService;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMock(): Repository
    {
        $repositoryMock = parent::getRepositoryMock();
        $repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->willReturn($this->getPermissionResolverMock());

        return $repositoryMock;
    }
}
