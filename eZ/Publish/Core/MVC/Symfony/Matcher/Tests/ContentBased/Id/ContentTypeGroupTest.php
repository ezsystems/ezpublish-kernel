<?php

/**
 * File containing the ContentTypeGroupTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ContentTypeGroup as ContentTypeGroupIdMatcher;
use eZ\Publish\Core\MVC\Symfony\Matcher\Tests\ContentBased\BaseTest;
use eZ\Publish\API\Repository\Repository;

class ContentTypeGroupTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ContentTypeGroup
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new ContentTypeGroupIdMatcher();
    }

    /**
     * @dataProvider matchLocationProvider
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ContentTypeGroup::matchLocation
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
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
        $data = array();

        $data[] = array(
            123,
            $this->generateRepositoryMockForContentTypeGroupId(123),
            true,
        );

        $data[] = array(
            123,
            $this->generateRepositoryMockForContentTypeGroupId(456),
            false,
        );

        $data[] = array(
            array(123, 789),
            $this->generateRepositoryMockForContentTypeGroupId(456),
            false,
        );

        $data[] = array(
            array(123, 789),
            $this->generateRepositoryMockForContentTypeGroupId(789),
            true,
        );

        return $data;
    }

    /**
     * Generates a Location mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateLocationMock()
    {
        $location = $this->getLocationMock();
        $location
            ->expects($this->any())
            ->method('getContentInfo')
            ->will(
                $this->returnValue(
                    $this->getContentInfoMock(array('contentTypeId' => 42))
                )
            );

        return $location;
    }

    /**
     * @dataProvider matchContentInfoProvider
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id\ContentTypeGroup::matchContentInfo
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued::setMatchingConfig
     *
     * @param int|int[] $matchingConfig
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param bool $expectedResult
     */
    public function testMatchContentInfo($matchingConfig, Repository $repository, $expectedResult)
    {
        $this->matcher->setRepository($repository);
        $this->matcher->setMatchingConfig($matchingConfig);

        $this->assertSame(
            $expectedResult,
            $this->matcher->matchContentInfo($this->getContentInfoMock(array('contentTypeId' => 42)))
        );
    }

    public function matchContentInfoProvider()
    {
        $data = array();

        $data[] = array(
            123,
            $this->generateRepositoryMockForContentTypeGroupId(123),
            true,
        );

        $data[] = array(
            123,
            $this->generateRepositoryMockForContentTypeGroupId(456),
            false,
        );

        $data[] = array(
            array(123, 789),
            $this->generateRepositoryMockForContentTypeGroupId(456),
            false,
        );

        $data[] = array(
            array(123, 789),
            $this->generateRepositoryMockForContentTypeGroupId(789),
            true,
        );

        return $data;
    }

    /**
     * Returns a Repository mock configured to return the appropriate Location object with given parent location Id.
     *
     * @param int $contentTypeGroupId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function generateRepositoryMockForContentTypeGroupId($contentTypeGroupId)
    {
        $contentTypeServiceMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\ContentTypeService')
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType');
        $contentTypeServiceMock->expects($this->once())
            ->method('loadContentType')
            ->with(42)
            ->will($this->returnValue($contentTypeMock));
        $contentTypeMock->expects($this->once())
            ->method('getContentTypeGroups')
            ->will(
                $this->returnValue(
                    array(
                        // First a group that will never match, then the right group.
                        // This ensures to test even if the content type belongs to several groups at once.
                        $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup'),
                        $this
                            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup')
                            ->setConstructorArgs(array(array('id' => $contentTypeGroupId)))
                            ->getMockForAbstractClass(),
                    )
                )
            );

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getContentTypeService')
            ->will($this->returnValue($contentTypeServiceMock));

        return $repository;
    }
}
