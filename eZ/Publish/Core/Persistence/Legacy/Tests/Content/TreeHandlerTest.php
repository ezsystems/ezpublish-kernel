<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\TreeHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Test case for Tree Handler.
 */
class TreeHandlerTest extends TestCase
{
    public function testLoadContentInfoByRemoteId()
    {
        $contentInfoData = array(new ContentInfo());

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(42)
            ->will($this->returnValue(array(42)));

        $this->getContentMapperMock()
            ->expects($this->once())
            ->method('extractContentInfoFromRow')
            ->with($this->equalTo(array(42)))
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
            ->will($this->returnValue(array(array('ezcontentobject_version_version' => 2))));

        $this->getContentGatewayMock()
            ->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo(array(array('id' => 23, 'version' => 2))))
            ->will($this->returnValue(array()));

        $this->getContentMapperMock()
            ->expects($this->once())
            ->method('extractVersionInfoListFromRows')
            ->with($this->equalTo(array(array('ezcontentobject_version_version' => 2))), array())
            ->will($this->returnValue(array(new VersionInfo())));

        $versions = $this->getTreeHandler()->listVersions(23);

        $this->assertEquals(
            array(new VersionInfo()),
            $versions
        );
    }

    public function testRemoveRawContent()
    {
        $treeHandler = $this->getPartlyMockedTreeHandler(
            array(
                'loadContentInfo',
                'listVersions',
            )
        );

        $treeHandler
            ->expects($this->once())
            ->method('listVersions')
            ->will($this->returnValue(array(new VersionInfo(), new VersionInfo())));
        $treeHandler
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(23)
            ->will($this->returnValue(new ContentInfo(array('mainLocationId' => 42))));

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
            array(
                'changeMainLocation',
                'removeRawContent',
            )
        );

        // Original call
        $this->getLocationGatewayMock()
            ->expects($this->at(0))
            ->method('getBasicNodeData')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        'contentobject_id' => 100,
                        'main_node_id' => 200,
                    )
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(1))
            ->method('getChildren')
            ->with(42)
            ->will(
                $this->returnValue(
                    array(
                        array('node_id' => 201),
                        array('node_id' => 202),
                    )
                )
            );

        // First recursive call
        $this->getLocationGatewayMock()
            ->expects($this->at(2))
            ->method('getBasicNodeData')
            ->with(201)
            ->will(
                $this->returnValue(
                    array(
                        'contentobject_id' => 101,
                        'main_node_id' => 201,
                    )
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(3))
            ->method('getChildren')
            ->with(201)
            ->will($this->returnValue(array()));
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
                    array(
                        'contentobject_id' => 102,
                        'main_node_id' => 202,
                    )
                )
            );
        $this->getLocationGatewayMock()
            ->expects($this->at(8))
            ->method('getChildren')
            ->with(202)
            ->will($this->returnValue(array()));
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
                    array(
                        'node_id' => 203,
                        'contentobject_version' => 1,
                        'parent_node_id' => 204,
                    )
                )
            );
        $treeHandler
            ->expects($this->once())
            ->method('changeMainLocation')
            ->with(102, 203, 1, 204);
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
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    )
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
            array(
                'loadLocation',
                'setSectionForSubtree',
                'loadContentInfo',
            )
        );

        $treeHandler
            ->expects($this->at(0))
            ->method('loadLocation')
            ->with(34)
            ->will($this->returnValue(new Location(array('parentId' => 42))));

        $treeHandler
            ->expects($this->at(1))
            ->method('loadContentInfo')
            ->with('12')
            ->will($this->returnValue(new ContentInfo(array('currentVersionNo' => 1))));

        $treeHandler
            ->expects($this->at(2))
            ->method('loadLocation')
            ->with(42)
            ->will($this->returnValue(new Location(array('contentId' => 84))));

        $treeHandler
            ->expects($this->at(3))
            ->method('loadContentInfo')
            ->with('84')
            ->will($this->returnValue(new ContentInfo(array('sectionId' => 4))));

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
                    array(
                        'node_id' => 77,
                    )
                )
            );

        $this->getLocationMapperMock()
            ->expects($this->once())
            ->method('createLocationFromRow')
            ->with(array('node_id' => 77))
            ->will($this->returnValue(new Location()));

        $location = $treeHandler->loadLocation(77);

        $this->assertTrue($location instanceof Location);
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGatewayMock;

    /**
     * Returns Location Gateway mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected function getLocationGatewayMock()
    {
        if (!isset($this->locationGatewayMock)) {
            $this->locationGatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway'
            );
        }

        return $this->locationGatewayMock;
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapperMock;

    /**
     * Returns a Location Mapper mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        if (!isset($this->locationMapperMock)) {
            $this->locationMapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }

        return $this->locationMapperMock;
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Returns Content Gateway mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if (!isset($this->contentGatewayMock)) {
            $this->contentGatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Gateway'
            );
        }

        return $this->contentGatewayMock;
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Returns a Content Mapper mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        if (!isset($this->contentMapper)) {
            $this->contentMapper = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }

        return $this->contentMapper;
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandlerMock;

    /**
     * Returns a FieldHandler mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandlerMock()
    {
        if (!isset($this->fieldHandlerMock)) {
            $this->fieldHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldHandler',
                array(),
                array(),
                '',
                false
            );
        }

        return $this->fieldHandlerMock;
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected function getPartlyMockedTreeHandler(array $methods)
    {
        return $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\TreeHandler',
            $methods,
            array(
                $this->getLocationGatewayMock(),
                $this->getLocationMapperMock(),
                $this->getContentGatewayMock(),
                $this->getContentMapperMock(),
                $this->getFieldHandlerMock(),
            )
        );
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
