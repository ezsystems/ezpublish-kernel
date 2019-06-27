<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\TreeHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;

/**
 * Test case for Tree Handler.
 */
class TreeHandlerTest extends TestCase
{
    public function testLoadContentInfoByRemoteId()
    {
        $contentInfoData = [new ContentInfo()];

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(42)
            ->will($this->returnValue([42]));

        $this->getContentMapperMock()
            ->expects($this->once())
            ->method('extractContentInfoFromRow')
            ->with($this->equalTo([42]))
            ->will($this->returnValue($contentInfoData));

        $this->assertSame(
            $contentInfoData,
            $this->getTreeHandler()->loadContentInfo(42)
        );
    }

    public function testListVersions()
    {
        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('listVersions')
            ->with($this->equalTo(23))
            ->will($this->returnValue([['ezcontentobject_version_version' => 2]]));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo([['id' => 23, 'version' => 2]]))
            ->will($this->returnValue([]));

        $this->getContentMapperMock()
            ->expects($this->once())
            ->method('extractVersionInfoListFromRows')
            ->with($this->equalTo([['ezcontentobject_version_version' => 2]]), [])
            ->will($this->returnValue([new VersionInfo()]));

        $versions = $this->getTreeHandler()->listVersions(23);

        $this->assertEquals(
            [new VersionInfo()],
            $versions
        );
    }

    public function testRemoveRawContent()
    {
        $treeHandler = $this->getPartlyMockedTreeHandler(
            [
                'loadContentInfo',
                'listVersions',
            ]
        );

        $treeHandler
            ->expects($this->once())
            ->method('listVersions')
            ->will($this->returnValue([new VersionInfo(), new VersionInfo()]));
        $treeHandler
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(23)
            ->will($this->returnValue(new ContentInfo(['mainLocationId' => 42])));

        $this->getFieldHandlerMock()
            ->expects($this->exactly(2))
            ->method('deleteFields')
            ->with(
                $this->equalTo(23),
                $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo')
            );

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteRelations')
            ->with($this->equalTo(23));
        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteVersions')
            ->with($this->equalTo(23));
        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteNames')
            ->with($this->equalTo(23));
        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('deleteContent')
            ->with($this->equalTo(23));

        $this->getLocationGatewayMock()
            ->expects($this->once())
            ->method('removeElementFromTrash')
            ->with($this->equalTo(42));

        $treeHandler->removeRawContent(23);
    }

    public function testRemoveSubtree()
    {
        $treeHandler = $this->getPartlyMockedTreeHandler(
            [
                'changeMainLocation',
                'removeRawContent',
            ]
        );

        // Original call
        $this->getLocationGatewayMock()
            ->expects($this->at(0))
            ->method('getBasicNodeData')
            ->with(42)
            ->will(
                $this->returnValue(
                    [
                        'contentobject_id' => 100,
                        'main_node_id' => 200,
                    ]
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(1))
            ->method('getChildren')
            ->with(42)
            ->will(
                $this->returnValue(
                    [
                        ['node_id' => 201],
                        ['node_id' => 202],
                    ]
                )
            );

        // First recursive call
        $this->getLocationGatewayMock()
            ->expects($this->at(2))
            ->method('getBasicNodeData')
            ->with(201)
            ->will(
                $this->returnValue(
                    [
                        'contentobject_id' => 101,
                        'main_node_id' => 201,
                    ]
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(3))
            ->method('getChildren')
            ->with(201)
            ->will($this->returnValue([]));
        $this->getLocationGatewayMock()
            ->expects($this->at(4))
            ->method('countLocationsByContentId')
            ->with(101)
            ->will($this->returnValue(1));
        $treeHandler
            ->expects($this->once())
            ->method('removeRawContent')
            ->with(101);
        $this->getLocationGatewayMock()
            ->expects($this->at(5))
            ->method('removeLocation')
            ->with(201);
        $this->getLocationGatewayMock()
            ->expects($this->at(6))
            ->method('deleteNodeAssignment')
            ->with(101);

        // Second recursive call
        $this->getLocationGatewayMock()
            ->expects($this->at(7))
            ->method('getBasicNodeData')
            ->with(202)
            ->will(
                $this->returnValue(
                    [
                        'contentobject_id' => 102,
                        'main_node_id' => 202,
                    ]
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(8))
            ->method('getChildren')
            ->with(202)
            ->will($this->returnValue([]));
        $this->getLocationGatewayMock()
            ->expects($this->at(9))
            ->method('countLocationsByContentId')
            ->with(102)
            ->will($this->returnValue(2));
        $this->getLocationGatewayMock()
            ->expects($this->at(10))
            ->method('getFallbackMainNodeData')
            ->with(102, 202)
            ->will(
                $this->returnValue(
                    [
                        'node_id' => 203,
                        'contentobject_version' => 1,
                        'parent_node_id' => 204,
                    ]
                )
            );
        $treeHandler
            ->expects($this->once())
            ->method('changeMainLocation')
            ->with(102, 203);
        $this->getLocationGatewayMock()
            ->expects($this->at(11))
            ->method('removeLocation')
            ->with(202);
        $this->getLocationGatewayMock()
            ->expects($this->at(12))
            ->method('deleteNodeAssignment')
            ->with(102);

        // Continuation of the original call
        $this->getLocationGatewayMock()
            ->expects($this->at(13))
            ->method('removeLocation')
            ->with(42);
        $this->getLocationGatewayMock()
            ->expects($this->at(14))
            ->method('deleteNodeAssignment')
            ->with(100);

        // Start
        $treeHandler->removeSubtree(42);
    }

    public function testSetSectionForSubtree()
    {
        $treeHandler = $this->getTreeHandler();

        $this->getLocationGatewayMock()
            ->expects($this->at(0))
            ->method('getBasicNodeData')
            ->with(69)
            ->will(
                $this->returnValue(
                    [
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    ]
                )
            );

        $this->getLocationGatewayMock()
            ->expects($this->once())
            ->method('setSectionForSubtree')
            ->with('/1/2/69/', 3);

        $treeHandler->setSectionForSubtree(69, 3);
    }

    public function testChangeMainLocation()
    {
        $treeHandler = $this->getPartlyMockedTreeHandler(
            [
                'loadLocation',
                'setSectionForSubtree',
                'loadContentInfo',
            ]
        );

        $treeHandler
            ->expects($this->at(0))
            ->method('loadLocation')
            ->with(34)
            ->will($this->returnValue(new Location(['parentId' => 42])));

        $treeHandler
            ->expects($this->at(1))
            ->method('loadContentInfo')
            ->with('12')
            ->will($this->returnValue(new ContentInfo(['currentVersionNo' => 1])));

        $treeHandler
            ->expects($this->at(2))
            ->method('loadLocation')
            ->with(42)
            ->will($this->returnValue(new Location(['contentId' => 84])));

        $treeHandler
            ->expects($this->at(3))
            ->method('loadContentInfo')
            ->with('84')
            ->will($this->returnValue(new ContentInfo(['sectionId' => 4])));

        $this->getLocationGatewayMock()
            ->expects($this->once())
            ->method('changeMainLocation')
            ->with(12, 34, 1, 42);

        $treeHandler
            ->expects($this->once())
            ->method('setSectionForSubtree')
            ->with(34, 4);

        $treeHandler->changeMainLocation(12, 34);
    }

    public function testLoadLocation()
    {
        $treeHandler = $this->getTreeHandler();

        $this->getLocationGatewayMock()
            ->expects($this->once())
            ->method('getBasicNodeData')
            ->with(77)
            ->will(
                $this->returnValue(
                    [
                        'node_id' => 77,
                    ]
                )
            );

        $this->getLocationMapperMock()
            ->expects($this->once())
            ->method('createLocationFromRow')
            ->with(['node_id' => 77])
            ->will($this->returnValue(new Location()));

        $location = $treeHandler->loadLocation(77);

        $this->assertTrue($location instanceof Location);
    }

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway */
    protected $locationGatewayMock;

    /**
     * Returns Location Gateway mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected function getLocationGatewayMock()
    {
        if (!isset($this->locationGatewayMock)) {
            $this->locationGatewayMock = $this->getMockForAbstractClass(LocationGateway::class);
        }

        return $this->locationGatewayMock;
    }

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper */
    protected $locationMapperMock;

    /**
     * Returns a Location Mapper mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        if (!isset($this->locationMapperMock)) {
            $this->locationMapperMock = $this->createMock(LocationMapper::class);
        }

        return $this->locationMapperMock;
    }

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway */
    protected $contentGatewayMock;

    /**
     * Returns Content Gateway mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if (!isset($this->contentGatewayMock)) {
            $this->contentGatewayMock = $this->getMockForAbstractClass(Gateway::class);
        }

        return $this->contentGatewayMock;
    }

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper */
    protected $contentMapper;

    /**
     * Returns a Content Mapper mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if (!isset($this->contentMapper)) {
            $this->contentMapper = $this->createMock(Mapper::class);
        }

        return $this->contentMapper;
    }

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler */
    protected $fieldHandlerMock;

    /**
     * Returns a FieldHandler mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandlerMock()
    {
        if (!isset($this->fieldHandlerMock)) {
            $this->fieldHandlerMock = $this->createMock(FieldHandler::class);
        }

        return $this->fieldHandlerMock;
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected function getPartlyMockedTreeHandler(array $methods)
    {
        return $this->getMockBuilder(TreeHandler::class)
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->getLocationGatewayMock(),
                    $this->getLocationMapperMock(),
                    $this->getContentGatewayMock(),
                    $this->getContentMapperMock(),
                    $this->getFieldHandlerMock(),
                ]
            )
            ->getMock();
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected function getTreeHandler()
    {
        return new TreeHandler(
            $this->getLocationGatewayMock(),
            $this->getLocationMapperMock(),
            $this->getContentGatewayMock(),
            $this->getContentMapperMock(),
            $this->getFieldHandlerMock()
        );
    }
}
