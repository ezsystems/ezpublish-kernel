<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\LocationHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group as ObjectStateGroup;

/**
 * Test case for LocationHandlerTest.
 */
class LocationHandlerTest extends TestCase
{
    /**
     * Mocked location gateway instance.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mocked location mapper instance.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Mocked content handler instance.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Mocked object state handler instance.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectStateHandler;

    /**
     * Mocked Tree handler instance.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $treeHandler;

    public function setUp()
    {
        parent::setUp();

        $this->locationGateway = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway');
        $this->locationMapper = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper');
        $this->treeHandler = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\TreeHandler', array(), array(), '', false);
    }

    protected function getLocationHandler()
    {
        $dbHandler = $this->getDatabaseHandler();

        return new Handler(
            $this->locationGateway,
            $this->locationMapper,
            $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler', array(), array(), '', false),
            $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\ObjectState\\Handler', array(), array(), '', false),
            $this->treeHandler
        );
    }

    public function testLoadLocation()
    {
        $handler = $this->getLocationHandler();

        $this->treeHandler
            ->expects($this->once())
            ->method('loadLocation')
            ->with(77)
            ->will($this->returnValue(new \eZ\Publish\SPI\Persistence\Content\Location()));

        $location = $handler->load(77);

        $this->assertTrue($location instanceof \eZ\Publish\SPI\Persistence\Content\Location);
    }

    public function testLoadLocationSubtree()
    {
        $this->locationGateway
            ->expects($this->once())
            ->method('getSubtreeContent')
            ->with(77, true)
            ->will(
                $this->returnValue(
                    array(
                        array(77 => 100),
                        array(78 => 101),
                    )
                )
            );

        $this->assertEquals(2, count($this->getLocationHandler()->loadSubtreeIds(77)));
    }

    public function testLoadLocationByRemoteId()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects($this->once())
            ->method('getBasicNodeDataByRemoteId')
            ->with('abc123')
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 77,
                    )
                )
            );

        $this->locationMapper
            ->expects($this->once())
            ->method('createLocationFromRow')
            ->with(array('node_id' => 77))
            ->will($this->returnValue(new \eZ\Publish\SPI\Persistence\Content\Location()));

        $location = $handler->loadByRemoteId('abc123');

        $this->assertTrue($location instanceof \eZ\Publish\SPI\Persistence\Content\Location);
    }

    public function testLoadLocationsByContent()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects($this->once())
            ->method('loadLocationDataByContent')
            ->with(23, 42)
            ->will(
                $this->returnValue(
                    array()
                )
            );

        $this->locationMapper
            ->expects($this->once())
            ->method('createLocationsFromRows')
            ->with(array())
            ->will($this->returnValue(array('a', 'b')));

        $locations = $handler->loadLocationsByContent(23, 42);

        $this->assertInternalType('array', $locations);
    }

    public function loadParentLocationsForDraftContent()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects($this->once())
            ->method('loadParentLocationsDataForDraftContent')
            ->with(23)
            ->will(
                $this->returnValue(
                    array()
                )
            );

        $this->locationMapper
            ->expects($this->once())
            ->method('createLocationsFromRows')
            ->with(array())
            ->will($this->returnValue(array('a', 'b')));

        $locations = $handler->loadParentLocationsForDraftContent(23);

        $this->assertInternalType('array', $locations);
    }

    public function testMoveSubtree()
    {
        $handler = $this->getLocationHandler();

        $sourceData = array(
            'node_id' => 69,
            'path_string' => '/1/2/69/',
            'parent_node_id' => 2,
            'contentobject_id' => 67,
        );
        $this->locationGateway
            ->expects($this->at(0))
            ->method('getBasicNodeData')
            ->with(69)
            ->will($this->returnValue($sourceData));

        $destinationData = array(
            'node_id' => 77,
            'path_string' => '/1/2/77/',
        );
        $this->locationGateway
            ->expects($this->at(1))
            ->method('getBasicNodeData')
            ->with(77)
            ->will($this->returnValue($destinationData));

        $this->locationGateway
            ->expects($this->once())
            ->method('moveSubtreeNodes')
            ->with($sourceData, $destinationData);

        $this->locationGateway
            ->expects($this->once())
            ->method('updateNodeAssignment')
            ->with(67, 2, 77, 5);

        $handler->move(69, 77);
    }

    public function testHideUpdateHidden()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
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

        $this->locationGateway
            ->expects($this->once())
            ->method('hideSubtree')
            ->with('/1/2/69/');

        $handler->hide(69);
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
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

        $this->locationGateway
            ->expects($this->once())
            ->method('unhideSubtree')
            ->with('/1/2/69/');

        $handler->unhide(69);
    }

    public function testSwapLocations()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects($this->once())
            ->method('swap')
            ->with(70, 78);

        $handler->swap(70, 78);
    }

    public function testCreateLocation()
    {
        $handler = $this->getLocationHandler();

        $createStruct = new CreateStruct();
        $createStruct->parentId = 77;

        $this->locationGateway
            ->expects($this->once())
            ->method('getBasicNodeData')
            ->with(77)
            ->will(
                $this->returnValue(
                    $parentInfo = array(
                        'node_id' => 77,
                        'path_string' => '/1/2/77/',
                    )
                )
            );

        $this->locationGateway
            ->expects($this->once())
            ->method('create')
            ->with($createStruct, $parentInfo)
            ->will($this->returnValue($createStruct));

        $this->locationGateway
            ->expects($this->once())
            ->method('createNodeAssignment')
            ->with($createStruct, 77, 2);

        $handler->create($createStruct);
    }

    public function testUpdateLocation()
    {
        $handler = $this->getLocationHandler();

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = 77;

        $this->locationGateway
            ->expects($this->once())
            ->method('update')
            ->with($updateStruct, 23);

        $handler->update($updateStruct, 23);
    }

    public function testSetSectionForSubtree()
    {
        $handler = $this->getLocationHandler();

        $this->treeHandler
            ->expects($this->once())
            ->method('setSectionForSubtree')
            ->with(69, 3);

        $handler->setSectionForSubtree(69, 3);
    }

    public function testMarkSubtreeModified()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
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

        $this->locationGateway
            ->expects($this->at(1))
            ->method('updateSubtreeModificationTime')
            ->with('/1/2/69/');

        $handler->markSubtreeModified(69);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::changeMainLocation
     */
    public function testChangeMainLocation()
    {
        $handler = $this->getLocationHandler();

        $this->treeHandler
            ->expects($this->once())
            ->method('changeMainLocation')
            ->with(12, 34);

        $handler->changeMainLocation(12, 34);
    }

    /**
     * Test for the removeSubtree() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::removeSubtree
     */
    public function testRemoveSubtree()
    {
        $handler = $this->getLocationHandler();

        $this->treeHandler
            ->expects($this->once())
            ->method('removeSubtree')
            ->with(42);

        $handler->removeSubtree(42);
    }

    /**
     * Test for the copySubtree() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::copySubtree
     */
    public function testCopySubtree()
    {
        $handler = $this->getPartlyMockedHandler(
            array(
                'load',
                'changeMainLocation',
                'setSectionForSubtree',
                'create',
            )
        );
        $subtreeContentRows = array(
            array('node_id' => 10, 'main_node_id' => 1, 'parent_node_id' => 3, 'contentobject_id' => 21, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_10', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 11, 'main_node_id' => 11, 'parent_node_id' => 10, 'contentobject_id' => 211, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_11', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 12, 'main_node_id' => 15, 'parent_node_id' => 10, 'contentobject_id' => 215, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_12', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 13, 'main_node_id' => 2, 'parent_node_id' => 10, 'contentobject_id' => 22, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_13', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 14, 'main_node_id' => 11, 'parent_node_id' => 13, 'contentobject_id' => 211, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_14', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 15, 'main_node_id' => 15, 'parent_node_id' => 13, 'contentobject_id' => 215, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_15', 'sort_field' => 2, 'sort_order' => 1),
            array('node_id' => 16, 'main_node_id' => 16, 'parent_node_id' => 15, 'contentobject_id' => 216, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 0, 'priority' => 0, 'path_identification_string' => 'test_16', 'sort_field' => 2, 'sort_order' => 1),
        );
        $destinationData = array('node_id' => 5, 'main_node_id' => 5, 'parent_node_id' => 4, 'contentobject_id' => 200, 'contentobject_version' => 1, 'is_hidden' => 0, 'is_invisible' => 1, 'path_identification_string' => 'test_destination');
        $mainLocationsMap = array(true, true, true, true, 1011, 1012, true);
        $updateMainLocationsMap = array(1215 => 1015);
        $offset = 1000;

        $this->locationGateway
            ->expects($this->once())
            ->method('getSubtreeContent')
            ->with($subtreeContentRows[0]['node_id'])
            ->will($this->returnValue($subtreeContentRows));
        $this->locationGateway
            ->expects($this->once())
            ->method('getBasicNodeData')
            ->with($destinationData['node_id'])
            ->will($this->returnValue($destinationData));

        $objectStateHandlerCall = 0;
        $this->objectStateHandler->expects($this->at($objectStateHandlerCall++))
            ->method('loadAllGroups')
            ->will(
                $this->returnValue(
                    array(
                        new ObjectStateGroup(array('id' => 10)),
                        new ObjectStateGroup(array('id' => 20)),
                    )
                )
            );
        $this->objectStateHandler->expects($this->at($objectStateHandlerCall++))
            ->method('loadObjectStates')
            ->with($this->equalTo(10))
            ->will(
                $this->returnValue(
                    array(
                        new ObjectState(array('id' => 11, 'groupId' => 10)),
                        new ObjectState(array('id' => 12, 'groupId' => 10)),
                    )
                )
            );
        $this->objectStateHandler->expects($this->at($objectStateHandlerCall++))
            ->method('loadObjectStates')
            ->with($this->equalTo(20))
            ->will(
                $this->returnValue(
                    array(
                        new ObjectState(array('id' => 21, 'groupId' => 20)),
                        new ObjectState(array('id' => 22, 'groupId' => 20)),
                    )
                )
            );
        $defaultObjectStates = array(
            new ObjectState(array('id' => 11, 'groupId' => 10)),
            new ObjectState(array('id' => 21, 'groupId' => 20)),
        );

        $contentIds = array_values(
            array_unique(
                array_map(
                    function ($row) {
                        return $row['contentobject_id'];
                    },
                    $subtreeContentRows
                )
            )
        );
        foreach ($contentIds as $index => $contentId) {
            $this->contentHandler
                ->expects($this->at($index * 2))
                ->method('copy')
                ->with($contentId, 1)
                ->will(
                    $this->returnValue(
                        new Content(
                            array(
                                'versionInfo' => new VersionInfo(
                                    array(
                                        'contentInfo' => new ContentInfo(
                                            array(
                                                'id' => $contentId + $offset,
                                                'currentVersionNo' => 1,
                                            )
                                        ),
                                    )
                                ),
                            )
                        )
                    )
                );

            foreach ($defaultObjectStates as $objectState) {
                $this->objectStateHandler->expects($this->at($objectStateHandlerCall++))
                    ->method('setContentState')
                    ->with(
                        $contentId + $offset,
                        $objectState->groupId,
                        $objectState->id
                    );
            }

            $this->contentHandler
                ->expects($this->at($index * 2 + 1))
                ->method('publish')
                ->with(
                    $contentId + $offset,
                    1,
                    $this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\MetadataUpdateStruct')
                )
                ->will(
                    $this->returnValue(
                        new Content(
                            array(
                                'versionInfo' => new VersionInfo(
                                    array(
                                        'contentInfo' => new ContentInfo(
                                            array(
                                                'id' => ($contentId + $offset),
                                            )
                                        ),
                                    )
                                ),
                            )
                        )
                    )
                );
        }
        $lastContentHandlerIndex = $index * 2 + 1;

        $pathStrings = array($destinationData['node_id'] => $destinationData['path_identification_string']);
        foreach ($subtreeContentRows as $index => $row) {
            $mapper = new Mapper();
            $createStruct = $mapper->getLocationCreateStruct($row);
            $this->locationMapper
                ->expects($this->at($index))
                ->method('getLocationCreateStruct')
                ->with($row)
                ->will($this->returnValue($createStruct));

            $createStruct = clone $createStruct;
            $createStruct->contentId = $createStruct->contentId + $offset;
            $createStruct->parentId = $index === 0 ? $destinationData['node_id'] : $createStruct->parentId + $offset;
            $createStruct->invisible = true;
            $createStruct->mainLocationId = $mainLocationsMap[$index];
            $createStruct->pathIdentificationString = $pathStrings[$createStruct->parentId] . '/' . $row['path_identification_string'];
            $pathStrings[$row['node_id'] + $offset] = $createStruct->pathIdentificationString;
            $handler
                ->expects($this->at($index))
                ->method('create')
                ->with($createStruct)
                ->will(
                    $this->returnValue(
                        new Location(
                            array(
                                'id' => $row['node_id'] + $offset,
                                'contentId' => $row['contentobject_id'],
                                'hidden' => false,
                                'invisible' => true,
                                'pathIdentificationString' => $createStruct->pathIdentificationString,
                            )
                        )
                    )
                );
        }

        foreach ($updateMainLocationsMap as $contentId => $locationId) {
            $handler
                ->expects($this->any())
                ->method('changeMainLocation')
                ->with($contentId, $locationId);
        }

        $this->contentHandler
            ->expects($this->at($lastContentHandlerIndex + 1))
            ->method('loadContentInfo')
            ->with(21)
            ->will($this->returnValue(new ContentInfo(array('mainLocationId' => 1010))));

        $handler
            ->expects($this->once())
            ->method('load')
            ->with($destinationData['node_id'])
            ->will($this->returnValue(new Location(array('contentId' => $destinationData['contentobject_id']))));

        $this->contentHandler
            ->expects($this->at($lastContentHandlerIndex + 2))
            ->method('loadContentInfo')
            ->with($destinationData['contentobject_id'])
            ->will($this->returnValue(new ContentInfo(array('sectionId' => 12345))));

        $handler
            ->expects($this->once())
            ->method('setSectionForSubtree')
            ->with($subtreeContentRows[0]['node_id'] + $offset, 12345);

        $handler->copySubtree(
            $subtreeContentRows[0]['node_id'],
            $destinationData['node_id']
        );
    }

    /**
     * Returns the handler to test with $methods mocked.
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    protected function getPartlyMockedHandler(array $methods)
    {
        return $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Handler',
            $methods,
            array(
                $this->locationGateway = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway', array(), array(), '', false),
                $this->locationMapper = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper', array(), array(), '', false),
                $this->contentHandler = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler', array(), array(), '', false),
                $this->objectStateHandler = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\ObjectState\\Handler', array(), array(), '', false),
                $this->treeHandler = $this->getMock('eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\TreeHandler', array(), array(), '', false),
            )
        );
    }
}
