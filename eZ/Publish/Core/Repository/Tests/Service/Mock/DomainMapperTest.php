<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\DomainMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Helper\DomainMapper;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;

/**
 * Mock test case for internal DomainMapper.
 */
class DomainMapperTest extends BaseServiceMockTest
{
    /**
     * @covers \eZ\Publish\Core\Repository\Helper\DomainMapper::buildVersionInfoDomainObject
     * @dataProvider providerForBuildVersionInfo
     */
    public function testBuildVersionInfo(SPIVersionInfo $spiVersionInfo, array $languages, array $expected)
    {
        $languageHandlerMock = $this->getLanguageHandlerMock();
        $languageHandlerMock->expects($this->never())->method('load');

        $versionInfo = $this->getDomainMapper()->buildVersionInfoDomainObject($spiVersionInfo);
        $this->assertInstanceOf(APIVersionInfo::class, $versionInfo);

        foreach ($expected as $expectedProperty => $expectedValue) {
            $this->assertAttributeSame(
                $expectedValue,
                $expectedProperty,
                $versionInfo
            );
        }
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Helper\DomainMapper::buildLocationWithContent
     */
    public function testBuildLocationWithContentForRootLocation()
    {
        $spiRootLocation = new Location(['id' => 1, 'parentId' => 1]);
        $apiRootLocation = $this->getDomainMapper()->buildLocationWithContent($spiRootLocation, null);

        $expectedContentInfo = new ContentInfo([
            'id' => 0,
        ]);
        $expectedContent = new Content();

        $this->assertInstanceOf(APILocation::class, $apiRootLocation);
        $this->assertEquals($spiRootLocation->id, $apiRootLocation->id);
        $this->assertEquals($expectedContentInfo->id, $apiRootLocation->getContentInfo()->id);
        $this->assertEquals($expectedContent, $apiRootLocation->getContent());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Helper\DomainMapper::buildLocationWithContent
     */
    public function testBuildLocationWithContentThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'$content\' is invalid: Location 2 has missing Content');

        $nonRootLocation = new Location(['id' => 2, 'parentId' => 1]);

        $this->getDomainMapper()->buildLocationWithContent($nonRootLocation, null);
    }

    public function testBuildLocationWithContentIsAlignedWithBuildLocation()
    {
        $spiRootLocation = new Location(['id' => 1, 'parentId' => 1]);

        $this->assertEquals(
            $this->getDomainMapper()->buildLocationWithContent($spiRootLocation, null),
            $this->getDomainMapper()->buildLocation($spiRootLocation)
        );
    }

    public function providerForBuildVersionInfo()
    {
        return [
            [
                new SPIVersionInfo(
                    [
                        'status' => 44,
                        'contentInfo' => new SPIContentInfo(),
                    ]
                ),
                [],
                ['status' => APIVersionInfo::STATUS_DRAFT],
            ],
            [
                new SPIVersionInfo(
                    [
                        'status' => SPIVersionInfo::STATUS_DRAFT,
                        'contentInfo' => new SPIContentInfo(),
                    ]
                ),
                [],
                ['status' => APIVersionInfo::STATUS_DRAFT],
            ],
            [
                new SPIVersionInfo(
                    [
                        'status' => SPIVersionInfo::STATUS_PENDING,
                        'contentInfo' => new SPIContentInfo(),
                    ]
                ),
                [],
                ['status' => APIVersionInfo::STATUS_DRAFT],
            ],
            [
                new SPIVersionInfo(
                    [
                        'status' => SPIVersionInfo::STATUS_ARCHIVED,
                        'contentInfo' => new SPIContentInfo(),
                        'languageCodes' => ['eng-GB', 'nor-NB', 'fre-FR'],
                    ]
                ),
                [1 => 'eng-GB', 3 => 'nor-NB', 5 => 'fre-FR'],
                [
                    'status' => APIVersionInfo::STATUS_ARCHIVED,
                    'languageCodes' => ['eng-GB', 'nor-NB', 'fre-FR'],
                ],
            ],
            [
                new SPIVersionInfo(
                    [
                        'status' => SPIVersionInfo::STATUS_PUBLISHED,
                        'contentInfo' => new SPIContentInfo(),
                    ]
                ),
                [],
                ['status' => APIVersionInfo::STATUS_PUBLISHED],
            ],
        ];
    }

    public function providerForBuildLocationDomainObjectsOnSearchResult()
    {
        $locationHits = [
            new Location(['id' => 21, 'contentId' => 32]),
            new Location(['id' => 22, 'contentId' => 33]),
        ];

        return [
            [$locationHits, [32, 33], [], [32 => new ContentInfo(['id' => 32]), 33 => new ContentInfo(['id' => 33])], 0],
            [$locationHits, [32, 33], ['languages' => ['eng-GB']], [32 => new ContentInfo(['id' => 32])], 1],
            [$locationHits, [32, 33], ['languages' => ['eng-GB']], [], 2],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Helper\DomainMapper::buildLocationDomainObjectsOnSearchResult
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
        $missingLocations = $this->getDomainMapper()->buildLocationDomainObjectsOnSearchResult($result, $languageFilter);
        $this->assertInternalType('array', $missingLocations);

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
     * Returns DomainMapper.
     *
     * @return \eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected function getDomainMapper()
    {
        return new DomainMapper(
            $this->getContentHandlerMock(),
            $this->getPersistenceMockHandler('Content\\Location\\Handler'),
            $this->getTypeHandlerMock(),
            $this->getContentTypeDomainMapperMock(),
            $this->getLanguageHandlerMock(),
            $this->getFieldTypeRegistryMock()
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
}
