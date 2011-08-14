<?php
/**
 * File contains: ezp\Content\Tests\Location\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Location;
use ezp\Content\Tests\BaseServiceTest,
    ezp\Content\Location\Service,
    ezp\Base\Exception\NotFound,
    \ReflectionObject,
    ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Content\Location,
    ezp\Content\Proxy;

/**
 * Test case for Location service
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Location\Service
     */
    protected $service;

    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     * @var \ezp\Content\Location
     */
    protected $topLocation;

    /**
     * @var \ezp\Content\Location
     */
    protected $location;

    /**
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

    /**
     * @var \ezp\Content\Location[]
     */
    protected $locationToDelete = array();

    /**
     * @var \ezp\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var \ezp\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Locations that have been created by insertSubtree()
     * @var \ezp\Content\Location[]
     */
    protected $insertedLocations = array();

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getLocationService();
        $this->locationHandler = $this->repositoryHandler->locationHandler();
        $this->contentHandler = $this->repositoryHandler->contentHandler();

        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                'type' => 'ezstring',
                // @todo Use FieldValue object
                'value' => 'Welcome',
                'language' => 'eng-GB',
            )
        );

        $this->content = $this->contentHandler->create( $struct );
        $this->contentToDelete[] = $this->content;

        // Now creating location for content
        $this->topLocation = $this->service->load( 2 );
        $this->location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $this->location->parent = $this->topLocation;
        $this->location = $this->service->create( $this->location );
        $this->locationToDelete[] = $this->location;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        try
        {
            // Removing default objects as well as those created by tests
            foreach ( $this->contentToDelete as $content )
            {
                $this->contentHandler->delete( $content->id );
            }
        $this->contentToDelete = array();

            foreach ( $this->locationToDelete as $location )
            {
                $this->locationHandler->delete( $location->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        $this->locationToDelete = array();
        $this->insertedLocations = array();

        parent::tearDown();
    }

    /**
     * Inserts a deep basic subtree
     */
    private function insertSubtree()
    {
        $parentId = $this->topLocation->id;
        for ( $i = 0; $i < 10; $i++ )
        {
            $struct = new CreateStruct();
            $struct->name = "foo$i";
            $struct->ownerId = 14;
            $struct->sectionId = 1;
            $struct->typeId = 2;
            $struct->fields[] = new Field(
                array(
                    'type' => 'ezstring',
                    // @todo Use FieldValue object
                    'value' => "bar$i",
                    'language' => 'eng-GB',
                )
            );
            $contentVO = $this->contentHandler->create( $struct );
            $this->contentToDelete[] = $contentVO;

            $location = new Location( new Proxy( $this->repository->getContentService(), $contentVO->id ) );
            $location->parent = $this->service->load( $parentId );
            $location = $this->service->create( $location );
            $this->locationToDelete[] = $location;
            $this->insertedLocations[] = $location;
            $parentId = $location->id;
        }
    }

    /**
     * This test assures that domain object is properly built with value object
     * returned by repository handler
     *
     * @group locationService
     * @covers ezp\Content\Location\Service::buildDomainObject
     */
    public function testBuildDomainObject()
    {
        $vo = $this->repositoryHandler->locationHandler()->load( 2 );

        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'buildDomainObject' );
        $refMethod->setAccessible( true );
        $do = $refMethod->invoke( $this->service, $vo );

        $refDo = new ReflectionObject( $do );
        $doRefProperties = $refDo->getProperty( 'properties' );
        $doRefProperties->setAccessible( true );
        $doProperties = $doRefProperties->getValue( $do );
        self::assertSame( $vo, $doProperties );

        $refParent = $refDo->getProperty( 'parent' );
        $refParent->setAccessible( true );
        $parent = $refParent->getValue( $do );
        self::assertInstanceOf( 'ezp\\Content\\Proxy', $parent, 'Parent location must be a valid Proxy object after init by service' );
        self::assertEquals( $vo->parentId, $parent->id );

        $refContent = $refDo->getProperty( 'content' );
        $refContent->setAccessible( true );
        $content = $refContent->getValue( $do );
        self::assertInstanceOf( 'ezp\\Content\\Proxy', $content, 'Content must be a valid Proxy object after init by service' );
        self::assertEquals( $vo->contentId, $content->id );

        self::assertEquals( $do->sortField, $vo->sortField );
        self::assertEquals( $do->sortOrder, $vo->sortOrder );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::load
     */
    public function testLoad()
    {
        self::assertInstanceOf( 'ezp\\Content\\Location', $this->service->load( 2 ) );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group locationService
     * @covers ezp\Content\Location\Service::load
     */
    public function testLoadNonExistent()
    {
        $do = $this->service->load( 0 );
    }

    /**
     * Test location creation
     * @group locationService
     * @covers ezp\Content\Location\Service::create
     */
    public function testCreate()
    {
        $remoteId = md5(microtime());
        $parent = $this->service->load( 2 );
        $location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $location->parent = $parent;
        $location->remoteId = $remoteId;
        $location->sortField = Location::SORT_FIELD_PRIORITY;
        $location->sortOrder = Location::SORT_ORDER_DESC;
        $location->priority = 100;

        $newLocation = $this->service->create( $location );
        $this->locationToDelete[] = $newLocation;
        $locationId = $newLocation->id;
        self::assertSame( $remoteId, $newLocation->remoteId );
        self::assertSame( 2, $newLocation->parentId );
        unset( $location, $newLocation );

        $location = $this->service->load( $locationId );
        self::assertInstanceOf( 'ezp\\Content\\Location', $location );
        self::assertEquals( $parent->pathString . $location->id . '/', $location->pathString );
        self::assertSame( 'test', $location->pathIdentificationString );

        // As $this->content already had a location ($this->location),
        // mainLocationId should be $this->location->id
        self::assertSame( $this->location->id, $location->mainLocationId );
        self::assertSame( $this->location->id, $this->location->mainLocationId );

        // Expected depth should be number of locations in pathString - 1 (first level doesn't count)
        $expectedDepth = count( explode( '/', substr( $location->pathString, 1, -1 ) ) ) - 1;
        self::assertequals( $expectedDepth, $location->depth );
        self::assertEquals( $parent->depth + 1, $location->depth );
    }

    /**
     * When creating a location, parent location is mandatory
     * @expectedException \ezp\Base\Exception\Logic
     * @group locationService
     * @covers ezp\Content\Location\Service::create
     */
    public function testCreateNoParent()
    {
        $location = new Location( new Proxy( $this->repository->getContentService(), 1 ) );
        $do = $this->service->create( $location );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::hide
     */
    public function testHide()
    {
        $time = time();
        // Setup a location for test content and delete local variable
        $locationForTestContent = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $locationForTestContent->parent = $this->topLocation;
        $locationForTestContent = $this->service->create( $locationForTestContent );
        $this->locationToDelete[] = $locationForTestContent;

        $hiddenLocation = $this->service->hide( $this->topLocation );
        self::assertInstanceOf( 'ezp\\Content\\Location' , $hiddenLocation );
        self::assertTrue( $hiddenLocation->hidden );
        self::assertTrue( $locationForTestContent->invisible );
        unset( $locationForTestContent );
        self::assertGreaterThanOrEqual( $time, $this->locationHandler->load( 2 )->modifiedSubLocation );

        // Try to create a new location under a hidden one.
        // Newly created location should be invisible
        $location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $location->parent = $this->topLocation;
        self::assertTrue( $this->service->create( $location )->invisible );
        $this->locationToDelete[] = $location;

        // Create a new location under an invisible one
        // New location should also be invisible
        $anotherLocation4AnotherContent = new Location( new Proxy( $this->repository->getContentService(), 1 ) );
        $anotherLocation4AnotherContent->parent = $location;
        self::assertTrue( $this->service->create( $anotherLocation4AnotherContent )->invisible );
        $this->locationToDelete[] = $anotherLocation4AnotherContent;
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::unhide
     */
    public function testUnhide()
    {
        $time = time();

        // Hide the main location
        $hiddenLocation = $this->service->hide( $this->topLocation );

        // Create a new location that will be hidden
        $locationShouldStayHidden = new Location( new Proxy( $this->repository->getContentService(), 1 ) );
        $locationShouldStayHidden->parent = $this->location;
        $this->service->create( $locationShouldStayHidden );
        $this->locationToDelete[] = $locationShouldStayHidden;
        $this->service->hide( $locationShouldStayHidden );

        // Create again a new location, under the last hidden one
        $locationShouldStayInvisible = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $locationShouldStayInvisible->parent = $locationShouldStayHidden;
        $locationShouldStayInvisible = $this->service->create( $locationShouldStayInvisible );

        // Now test
        $parentMadeVisible = $this->service->unhide( $this->topLocation );
        self::assertInstanceOf( 'ezp\\Content\\Location' , $parentMadeVisible );
        self::assertFalse( $this->location->invisible );
        self::assertFalse( $this->location->hidden );
        self::assertTrue( $locationShouldStayHidden->hidden && $locationShouldStayHidden->invisible,
                          'A hidden location should not be made visible by superior location' );
        self::assertTrue( $locationShouldStayInvisible->invisible );
        self::assertGreaterThanOrEqual( $time, $this->locationHandler->load( $this->topLocation->id )->modifiedSubLocation );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::swap
     */
    public function testSwap()
    {
        $topContentId = $this->topLocation->contentId;
        $topContentName = $this->topLocation->content->name;
        $topLocationId = $this->topLocation->id;
        $contentId = $this->location->contentId;
        $contentName = $this->location->content->name;
        $locationId = $this->location->id;

        $this->service->swap( $this->topLocation, $this->location );

        self::assertSame( $topContentId, $this->location->contentId );
        self::assertSame( $topContentName, $this->location->content->name );
        self::assertSame( $contentId, $this->topLocation->contentId );
        self::assertSame( $contentName, $this->topLocation->content->name );
        self::assertSame( $topLocationId, $this->topLocation->id, 'Swapped locations keep same Ids' );
        self::assertSame( $locationId, $this->location->id, 'Swapped locations keep same Ids' );
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::refreshDomainObject
     */
    public function testRefreshDomainObjectWithoutArg()
    {
        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'refreshDomainObject' );
        $refMethod->setAccessible( true );

        $stateBeforeEdit = $this->service->load( 2 )->getState();
        $voBeforeEdit = (array)$stateBeforeEdit['properties'];

        $this->topLocation->remoteId = 'anotherRemoteId';
        $this->topLocation->priority = 357;
        $refreshedLocation = $refMethod->invoke( $this->service, $this->topLocation );
        $newState = $refreshedLocation->getState();
        self::assertSame( $voBeforeEdit, (array)$newState['properties'] );
    }

    /**
     * Test LocationService::refreshDomainObject() by injecting a different VO
     *
     * @group locationService
     * @covers \ezp\Content\Location\Service::refreshDomainObject
     */
    public function testRefreshDomainObjectWithArg()
    {
        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'refreshDomainObject' );
        $refMethod->setAccessible( true );

        $stateDifferentLocation = $this->location->getState();
        $voDifferentLocation = $stateDifferentLocation['properties'];

        $refreshedLocation = $refMethod->invoke( $this->service, $this->topLocation, $voDifferentLocation );
        $newState = $refreshedLocation->getState();
        self::assertSame( (array)$voDifferentLocation, (array)$newState['properties'] );
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::update
     */
    public function testUpdate()
    {
        $newRemoteId = 'anotherRemoteId';
        $newPriority = 357;
        $newSortField = Location::SORT_FIELD_DEPTH;
        $newSortOrder = Location::SORT_ORDER_DESC;
        $locationId = $this->location->id;

        $this->location->remoteId = $newRemoteId;
        $this->location->priority = $newPriority;
        $this->location->sortField = $newSortField;
        $this->location->sortOrder = $newSortOrder;
        $this->service->update( $this->location );
        unset( $this->location );

        $reloadedLocation = $this->service->load( $locationId );
        self::assertSame( $newRemoteId, $reloadedLocation->remoteId );
        self::assertSame( $newPriority, $reloadedLocation->priority );
        self::assertSame( $newSortField, $reloadedLocation->sortField );
        self::assertSame( $newSortOrder, $reloadedLocation->sortOrder );
    }

    /**
     * @group locationService
     * @expectedException \ezp\Base\Exception\Logic
     * @covers \ezp\Content\Location\Service::update
     */
    public function testUpdateFail()
    {
        $state = $this->location->getState();
        $state['properties']->id = 123456789;
        $this->location->setState( array( 'properties' => $state['properties'] ) );
        $this->service->update( $this->location );
    }

    /**
     * @group locationService
     */
    public function testMove()
    {
        $this->insertSubtree();
        $startIndex = 5;
        $locationToMove = $this->insertedLocations[$startIndex];
        $this->service->move( $locationToMove, $this->topLocation );
        self::assertEquals(
            $this->topLocation->pathString . $locationToMove->id . '/',
            $locationToMove->pathString
        );
        self::assertSame( $this->topLocation->id, $locationToMove->parentId );
        // @todo: test pathIdentificationString

        $parentId = $locationToMove->id;
        $parentPathString = $locationToMove->pathString;
        foreach ( array_splice( $this->insertedLocations, $startIndex + 1 ) as $key => $location )
        {
            $location = $this->service->load( $location->id );
            self::assertEquals( $parentId, $location->parentId );
            self::assertEquals( $parentPathString . $location->id . '/', $location->pathString );
            // @todo: test pathIdentificationString
            $parentId = $location->id;
            $parentPathString = $location->pathString;
        }
    }

    /**
     * @group locationService
     */
    public function testDelete()
    {
        $this->insertSubtree();
        $startIndex = 5;
        $this->service->delete( $this->insertedLocations[$startIndex] );


        foreach ( array_splice( $this->insertedLocations, $startIndex ) as $key => $location )
        {
            try
            {
                $this->service->load( $location->id );
                $this->fail( "Location #{$location->id} has not been properly removed" );
            }
            catch( NotFound $e )
            {
            }

            try
            {
                $this->contentHandler->load( $location->contentId, 1 );
                $this->fail( "Content #{$location->contentId} has not been properly removed" );
            }
            catch( NotFound $e )
            {
            }
        }

        // Create a secondary location for one the inserted locations
        // Delete the previous one and check if only location has been removed
        $startIndex--;
        $newLocation = new Location( new Proxy( $this->repository->getContentService(), $this->insertedLocations[$startIndex]->contentId ) );
        $newLocation->parent = $this->topLocation;
        $newLocation = $this->service->create( $newLocation );
        $this->service->delete( $this->insertedLocations[$startIndex] );
        // Reload location from backend
        $newLocation = $this->service->load( $newLocation->id );
        self::assertSame( $newLocation->id, $newLocation->mainLocationId );
    }
}
