<?php

/**
 * File containing the SectionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Identifier;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\Section as SectionIdentifierMatcher;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\API\Repository\Repository;

class SectionTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\Section */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new SectionIdentifierMatcher();
    }

    /**
     * Returns a Repository mock configured to return the appropriate Section object with given section identifier.
     *
     * @param string $sectionIdentifier
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateRepositoryMockForSectionIdentifier($sectionIdentifier)
    {
        $sectionServiceMock = $this->createMock(SectionService::class);
        $sectionServiceMock->expects($this->once())
            ->method('loadSection')
            ->will(
                $this->returnValue(
                    $this
                        ->getMockBuilder(Section::class)
                        ->setConstructorArgs(
                            [
                                ['identifier' => $sectionIdentifier],
                            ]
                        )
                        ->getMockForAbstractClass()
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getSectionService')
            ->will($this->returnValue($sectionServiceMock));
        $repository
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        return $repository;
    }

    /**
     * @dataProvider matchSectionProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\Section::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param bool $expectedResult
     */
    public function testMatchLocation($matchingConfig, Repository $repository, $expectedResult)
    {
        $this->matcher->setRepository($repository);
        $this->matcher->setMatchingConfig($matchingConfig);
        $location = $this->getLocationMock();
        $location
            ->expects($this->once())
            ->method('getContentInfo')
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock()
                )
            );
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchLocation($location)
        );
    }

    public function matchSectionProvider()
    {
        return [
            [
                'foo',
                $this->generateRepositoryMockForSectionIdentifier('foo'),
                true,
            ],
            [
                'foo',
                $this->generateRepositoryMockForSectionIdentifier('bar'),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->generateRepositoryMockForSectionIdentifier('bar'),
                false,
            ],
            [
                ['foo', 'baz'],
                $this->generateRepositoryMockForSectionIdentifier('baz'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider matchSectionProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\Section::matchContentInfo
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\RepositoryAware::setRepository
     *
     * @param string|string[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param bool $expectedResult
     */
    public function testMatchContentInfo($matchingConfig, Repository $repository, $expectedResult)
    {
        $this->matcher->setRepository($repository);
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchContentInfo($this->getContentInfoMock())
        );
    }
}
