<?php

/**
 * File containing the ContentTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Identifier;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\ContentType as ContentTypeIdentifierMatcher;
use eZ\Publish\API\Repository\Repository;

class ContentTypeTest extends BaseTest
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\ContentType */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentTypeIdentifierMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\ContentType::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
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
            $this->matcher->matchLocation($this->generateLocationMock())
        );
    }

    public function matchLocationProvider()
    {
        $data = [];

        $data[] = [
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier('foo'),
            true,
        ];

        $data[] = [
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier('bar'),
            false,
        ];

        $data[] = [
            ['foo', 'baz'],
            $this->generateRepositoryMockForContentTypeIdentifier('bar'),
            false,
        ];

        $data[] = [
            ['foo', 'baz'],
            $this->generateRepositoryMockForContentTypeIdentifier('baz'),
            true,
        ];

        return $data;
    }

    /**
     * Generates a Location object in respect of a given content type identifier.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateLocationMock()
    {
        $location = $this->getLocationMock();
        $location
            ->expects($this->any())
            ->method('getContentInfo')
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock(['contentTypeId' => 42])
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier\ContentType::matchLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
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
            $this->matcher->matchContentInfo(
                $this->getContentInfoMock(['contentTypeId' => 42])
            )
        );
    }

    public function matchContentInfoProvider()
    {
        $data = [];

        $data[] = [
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier('foo'),
            true,
        ];

        $data[] = [
            'foo',
            $this->generateRepositoryMockForContentTypeIdentifier('bar'),
            false,
        ];

        $data[] = [
            ['foo', 'baz'],
            $this->generateRepositoryMockForContentTypeIdentifier('bar'),
            false,
        ];

        $data[] = [
            ['foo', 'baz'],
            $this->generateRepositoryMockForContentTypeIdentifier('baz'),
            true,
        ];

        return $data;
    }

    /**
     * Returns a Repository mock configured to return the appropriate ContentType object with given identifier.
     *
     * @param int $contentTypeIdentifier
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateRepositoryMockForContentTypeIdentifier($contentTypeIdentifier)
    {
        $contentTypeMock = $this
            ->getMockBuilder(ContentType::class)
            ->setConstructorArgs(
                [['identifier' => $contentTypeIdentifier]]
            )
            ->getMockForAbstractClass();
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with(42)
            ->will(
                $this->returnValue($contentTypeMock)
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        return $repository;
    }
}
