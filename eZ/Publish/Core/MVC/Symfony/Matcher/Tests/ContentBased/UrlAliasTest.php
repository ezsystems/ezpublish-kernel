<?php

/**
 * File containing the UrlAliasTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias as UrlAliasMatcher;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\API\Repository\Repository;

class UrlAliasTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new UrlAliasMatcher();
    }

    /**
     * @dataProvider setMatchingConfigProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias::setMatchingConfig
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param string $matchingConfig
     * @param string[] $expectedValues
     */
    public function testSetMatchingConfig($matchingConfig, $expectedValues)
    {
        $this->matcher->setMatchingConfig($matchingConfig);
        $this->assertSame(
            $this->matcher->getValues(),
            $expectedValues
        );
    }

    public function setMatchingConfigProvider()
    {
        return [
            ['/foo/bar/', ['foo/bar']],
            ['/foo/bar/', ['foo/bar']],
            ['/foo/bar', ['foo/bar']],
            [['/foo/bar/', 'baz/biz/'], ['foo/bar', 'baz/biz']],
            [['foo/bar', 'baz/biz'], ['foo/bar', 'baz/biz']],
        ];
    }

    /**
     * Returns a Repository mock configured to return the appropriate Section object with given section identifier.
     *
     * @param string $path
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateRepositoryMockForUrlAlias($path)
    {
        // First an url alias that will never match, then the right url alias.
        // This ensures to test even if the location has several url aliases.
        $urlAliasList = [
            $this->createMock(URLAlias::class),
            $this
                ->getMockBuilder(URLAlias::class)
                ->setConstructorArgs([['path' => $path]])
                ->getMockForAbstractClass(),
        ];

        $urlAliasServiceMock = $this->createMock(URLAliasService::class);
        $urlAliasServiceMock->expects($this->at(0))
            ->method('listLocationAliases')
            ->with(
                $this->isInstanceOf(Location::class),
                true
            )
            ->will($this->returnValue([]));
        $urlAliasServiceMock->expects($this->at(1))
            ->method('listLocationAliases')
            ->with(
                $this->isInstanceOf(Location::class),
                false
            )
            ->will($this->returnValue($urlAliasList));

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getURLAliasService')
            ->will($this->returnValue($urlAliasServiceMock));

        return $repository;
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias::setMatchingConfig
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
        $this->assertSame(
            $expectedResult,
            $this->matcher->matchLocation($this->getLocationMock())
        );
    }

    public function matchLocationProvider()
    {
        return [
            [
                'foo/url',
                $this->generateRepositoryMockForUrlAlias('/foo/url'),
                true,
            ],
            [
                '/foo/url',
                $this->generateRepositoryMockForUrlAlias('/foo/url'),
                true,
            ],
            [
                'foo/url',
                $this->generateRepositoryMockForUrlAlias('/bar/url'),
                false,
            ],
            [
                ['foo/url', 'baz'],
                $this->generateRepositoryMockForUrlAlias('/bar/url'),
                false,
            ],
            [
                ['foo/url   ', 'baz   '],
                $this->generateRepositoryMockForUrlAlias('/baz'),
                true,
            ],
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias::matchContentInfo
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\UrlAlias::setMatchingConfig
     */
    public function testMatchContentInfo()
    {
        $this->matcher->setMatchingConfig('foo/bar');
        $this->matcher->matchContentInfo($this->getContentInfoMock());
    }
}
