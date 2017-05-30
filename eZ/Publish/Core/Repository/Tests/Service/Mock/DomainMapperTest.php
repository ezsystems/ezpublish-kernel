<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\DomainMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Helper\DomainMapper;
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
        $this->assertInstanceOf('eZ\\Publish\\Core\\Repository\\Values\\Content\\VersionInfo', $versionInfo);

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

    /**
     * Returns DomainMapper.
     *
     * @return \eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected function getDomainMapper()
    {
        return new DomainMapper(
            $this->getPersistenceMockHandler('Content\\Handler'),
            $this->getPersistenceMockHandler('Content\\Location\\Handler'),
            $this->getTypeHandlerMock(),
            $this->getLanguageHandlerMock(),
            $this->getFieldTypeRegistryMock()
        );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLanguageHandlerMock()
    {
        return $this->getPersistenceMockHandler('Content\\Language\\Handler');
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeHandlerMock()
    {
        return $this->getPersistenceMockHandler('Content\\Type\\Handler');
    }
}
