<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\DomainMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use DateTime;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Mapper\ContentDomainMapper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;

/**
 * Mock test case for internal ContentDomainMapper.
 */
class DomainMapperTest extends BaseServiceMockTest
{
    private const EXAMPLE_CONTENT_TYPE_ID = 1;
    private const EXAMPLE_SECTION_ID = 1;
    private const EXAMPLE_MAIN_LOCATION_ID = 1;
    private const EXAMPLE_MAIN_LANGUAGE_CODE = 'ger-DE';
    private const EXAMPLE_OWNER_ID = 1;
    private const EXAMPLE_INITIAL_LANGUAGE_CODE = 'eng-GB';
    private const EXAMPLE_CREATOR_ID = 23;

    /**
     * @covers \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper::buildVersionInfoDomainObject
     * @dataProvider providerForBuildVersionInfo
     */
    public function testBuildVersionInfo(SPIVersionInfo $spiVersionInfo)
    {
        $languageHandlerMock = $this->getLanguageHandlerMock();
        $languageHandlerMock->expects($this->never())->method('load');

        $versionInfo = $this->getContentDomainMapper()->buildVersionInfoDomainObject($spiVersionInfo);

        $this->assertInstanceOf(APIVersionInfo::class, $versionInfo);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper::buildLocationWithContent
     */
    public function testBuildLocationWithContentForRootLocation()
    {
        $spiRootLocation = new Location(['id' => 1, 'parentId' => 1]);
        $apiRootLocation = $this->getContentDomainMapper()->buildLocationWithContent($spiRootLocation, null);

        $legacyDateTime = new DateTime();
        $legacyDateTime->setTimestamp(1030968000);

        $expectedContentInfo = new \eZ\Publish\API\Repository\Values\Content\ContentInfo([
            'id' => 0,
            'name' => 'Top Level Nodes',
            'sectionId' => 1,
            'mainLocationId' => 1,
            'contentTypeId' => 1,
            'currentVersionNo' => 1,
            'published' => 1,
            'ownerId' => 14,
            'modificationDate' => $legacyDateTime,
            'publishedDate' => $legacyDateTime,
            'alwaysAvailable' => 1,
            'remoteId' => null,
            'mainLanguageCode' => 'eng-GB',
        ]);

        $expectedContent = new Content([
            'versionInfo' => new VersionInfo([
                'names' => [
                    $expectedContentInfo->mainLanguageCode => $expectedContentInfo->name,
                ],
                'contentInfo' => $expectedContentInfo,
                'versionNo' => $expectedContentInfo->currentVersionNo,
                'modificationDate' => $expectedContentInfo->modificationDate,
                'creationDate' => $expectedContentInfo->modificationDate,
                'creatorId' => $expectedContentInfo->ownerId,
            ]),
        ]);

        $this->assertInstanceOf(APILocation::class, $apiRootLocation);
        $this->assertEquals($spiRootLocation->id, $apiRootLocation->id);
        $this->assertEquals($expectedContentInfo->id, $apiRootLocation->getContentInfo()->id);
        $this->assertEquals($expectedContent, $apiRootLocation->getContent());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper::buildLocationWithContent
     */
    public function testBuildLocationWithContentThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'$content\' is invalid: Location 2 has missing Content');

        $nonRootLocation = new Location(['id' => 2, 'parentId' => 1]);

        $this->getContentDomainMapper()->buildLocationWithContent($nonRootLocation, null);
    }

    public function testBuildLocationWithContentIsAlignedWithBuildLocation()
    {
        $spiRootLocation = new Location(['id' => 1, 'parentId' => 1]);

        $this->assertEquals(
            $this->getContentDomainMapper()->buildLocationWithContent($spiRootLocation, null),
            $this->getContentDomainMapper()->buildLocation($spiRootLocation)
        );
    }

    public function providerForBuildVersionInfo()
    {
        $properties = [
            'contentInfo' => new SPIContentInfo([
                'contentTypeId' => self::EXAMPLE_CONTENT_TYPE_ID,
                'sectionId' => self::EXAMPLE_SECTION_ID,
                'mainLocationId' => self::EXAMPLE_MAIN_LOCATION_ID,
                'mainLanguageCode' => self::EXAMPLE_MAIN_LANGUAGE_CODE,
                'ownerId' => self::EXAMPLE_OWNER_ID,
            ]),
            'creatorId' => self::EXAMPLE_CREATOR_ID,
            'initialLanguageCode' => self::EXAMPLE_INITIAL_LANGUAGE_CODE,
        ];

        return [
            [
                new SPIVersionInfo(
                    $properties + [
                        'status' => 44,
                    ]),
            ],
            [
                new SPIVersionInfo(
                    $properties + [
                        'status' => SPIVersionInfo::STATUS_DRAFT,
                    ]
                ),
            ],
            [
                new SPIVersionInfo(
                    $properties + [
                        'status' => SPIVersionInfo::STATUS_PENDING,
                    ]
                ),
            ],
            [
                new SPIVersionInfo(
                    $properties + [
                        'status' => SPIVersionInfo::STATUS_ARCHIVED,
                        'languageCodes' => ['eng-GB', 'nor-NB', 'fre-FR'],
                    ]
                ),
            ],
            [
                new SPIVersionInfo(
                    $properties + [
                        'status' => SPIVersionInfo::STATUS_PUBLISHED,
                    ]
                ),
            ],
        ];
    }

    public function providerForBuildLocationDomainObjectsOnSearchResult()
    {
        $properties = [
            'contentTypeId' => self::EXAMPLE_CONTENT_TYPE_ID,
            'sectionId' => self::EXAMPLE_SECTION_ID,
            'mainLocationId' => self::EXAMPLE_MAIN_LOCATION_ID,
            'mainLanguageCode' => self::EXAMPLE_MAIN_LANGUAGE_CODE,
            'ownerId' => self::EXAMPLE_OWNER_ID,
        ];

        $locationHits = [
            new Location(['id' => 21, 'contentId' => 32, 'parentId' => 1]),
            new Location(['id' => 22, 'contentId' => 33, 'parentId' => 1]),
        ];

        return [
            [
                $locationHits,
                [32, 33],
                [],
                [
                    32 => new ContentInfo($properties + ['id' => 32]),
                    33 => new ContentInfo($properties + ['id' => 33]),
                ],
                0,
            ],
            [
                $locationHits,
                [32, 33],
                ['languages' => ['eng-GB']],
                [
                    32 => new ContentInfo($properties + ['id' => 32]),
                ],
                1,
            ],
            [
                $locationHits,
                [32, 33],
                ['languages' => ['eng-GB']],
                [],
                2,
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper::buildLocationDomainObjectsOnSearchResult
     * @dataProvider providerForBuildLocationDomainObjectsOnSearchResult
     *
     * @param array $locationHits
     * @param array $contentIds
     * @param array $languageFilter
     * @param array $contentInfoList
     * @param int $missing
     */
    public function testBuildLocationDomainObjectsOnSearchResult(
        array $locationHits,
        array $contentIds,
        array $languageFilter,
        array $contentInfoList,
        int $missing
    ) {
        $contentHandlerMock = $this->getContentHandlerMock();
        $contentHandlerMock
            ->expects($this->once())
            ->method('loadContentInfoList')
            ->with($contentIds)
            ->willReturn($contentInfoList);

        $result = new SearchResult(['totalCount' => 10]);
        foreach ($locationHits as $locationHit) {
            $result->searchHits[] = new SearchHit(['valueObject' => $locationHit]);
        }

        $spiResult = clone $result;
        $missingLocations = $this->getContentDomainMapper()->buildLocationDomainObjectsOnSearchResult($result, $languageFilter);
        $this->assertIsArray($missingLocations);

        if (!$missing) {
            $this->assertEmpty($missingLocations);
        } else {
            $this->assertNotEmpty($missingLocations);
        }

        $this->assertCount($missing, $missingLocations);
        $this->assertEquals($spiResult->totalCount - $missing, $result->totalCount);
        $this->assertCount(count($spiResult->searchHits) - $missing, $result->searchHits);
    }

    /**
     * Returns ContentDomainMapper.
     *
     * @return \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper
     */
    protected function getContentDomainMapper(): ContentDomainMapper
    {
        return new ContentDomainMapper(
            $this->getContentHandlerMock(),
            $this->getPersistenceMockHandler('Content\\Location\\Handler'),
            $this->getTypeHandlerMock(),
            $this->getContentTypeDomainMapperMock(),
            $this->getLanguageHandlerMock(),
            $this->getFieldTypeRegistryMock(),
            $this->getThumbnailStrategy(),
            $this->getProxyFactoryMock()
        );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContentHandlerMock()
    {
        return $this->getPersistenceMockHandler('Content\\Handler');
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLanguageHandlerMock()
    {
        return $this->getPersistenceMockHandler('Content\\Language\\Handler');
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTypeHandlerMock()
    {
        return $this->getPersistenceMockHandler('Content\\Type\\Handler');
    }

    protected function getProxyFactoryMock(): ProxyDomainMapperInterface
    {
        return $this->createMock(ProxyDomainMapperInterface::class);
    }
}
