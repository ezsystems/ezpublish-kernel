<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\DomainMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Helper\DomainMapper;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
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

    public function providerForBuildVersionInfo()
    {
        return array(
            array(
                new SPIVersionInfo(
                    array(
                        'status' => 44,
                        'contentInfo' => new SPIContentInfo(),
                    )
                ),
                array(),
                array('status' => APIVersionInfo::STATUS_DRAFT),
            ),
            array(
                new SPIVersionInfo(
                    array(
                        'status' => SPIVersionInfo::STATUS_DRAFT,
                        'contentInfo' => new SPIContentInfo(),
                    )
                ),
                array(),
                array('status' => APIVersionInfo::STATUS_DRAFT),
            ),
            array(
                new SPIVersionInfo(
                    array(
                        'status' => SPIVersionInfo::STATUS_PENDING,
                        'contentInfo' => new SPIContentInfo(),
                    )
                ),
                array(),
                array('status' => APIVersionInfo::STATUS_DRAFT),
            ),
            array(
                new SPIVersionInfo(
                    array(
                        'status' => SPIVersionInfo::STATUS_ARCHIVED,
                        'contentInfo' => new SPIContentInfo(),
                        'languageCodes' => array('eng-GB', 'nor-NB', 'fre-FR'),
                    )
                ),
                array(1 => 'eng-GB', 3 => 'nor-NB', 5 => 'fre-FR'),
                array(
                    'status' => APIVersionInfo::STATUS_ARCHIVED,
                    'languageCodes' => array('eng-GB', 'nor-NB', 'fre-FR'),
                ),
            ),
            array(
                new SPIVersionInfo(
                    array(
                        'status' => SPIVersionInfo::STATUS_PUBLISHED,
                        'contentInfo' => new SPIContentInfo(),
                    )
                ),
                array(),
                array('status' => APIVersionInfo::STATUS_PUBLISHED),
            ),
        );
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
