<?php
/**
 * File contains: ezp\Content\Tests\Service\LocationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Proxy as ProxyContent,
    ezp\Content\Location,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Section\Concrete as ConcreteSection,
    ezp\Base\Exception\NotFound,
    ezp\User\Proxy as ProxyUser,
    ReflectionObject;

/**
 * Test case for Location service
 */
class LocationTest extends BaseServiceTest
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
     * @var \ezp\User
     */
    protected $anonymous;

    /**
     * @var \ezp\User
     */
    protected $administrator;

    /**
     * Locations that have been created by setUp() and tests
     *
     * @var \ezp\Content\Location[]
     */
    protected $insertedLocations = array();

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getLocationService();
        $this->administrator = $this->repository->getUserService()->load( 14 );
        $this->anonymous = $this->repository->setUser( $this->administrator );// "Login" admin

        $type = $this->repository->getContentTypeService()->load( 1 );
        $section = $this->repository->getSectionService()->load( 1 );
        $content = new ConcreteContent( $type, $this->administrator );
        $content->setSection( $section );
        $fields = $content->getCurrentVersion()->getFields();
        $fields['name'] = 'Welcome';

        $this->content = $this->repository->getContentService()->create( $content );
        $this->contentToDelete[] = $this->content;

        // Now creating location for content
        $this->topLocation = $this->service->load( 2 );
        $this->location = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $this->location->setParent( $this->topLocation );
        $this->location = $this->service->create( $this->location );
        $this->locationToDelete[] = $this->location;

        $parent = $this->topLocation;
        for ( $i = 0; $i < 10; ++$i )
        {
            $content = new ConcreteContent( $type, $this->administrator );
            $content->setSection( $section );
            $fields = $content->getCurrentVersion()->getFields();
            $fields['name'] = "bar$i";

            $content = $this->repository->getContentService()->create( $content );
            $this->contentToDelete[] = $content;

            $location = new ConcreteLocation( $content );
            $location->setParent( $parent );
            $location = $this->service->create( $location );
            $this->locationToDelete[] = $location;
            $this->insertedLocations[] = $location;
            $parent = $location;
        }
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $this->repository->setUser( $this->administrator );
        // Removing default objects as well as those created by tests
        foreach ( $this->contentToDelete as $content )
        {
            try
            {
                $this->repository->getContentService()->delete( $content );
            }
            catch ( NotFound $e )
            {
            }
        }
        $this->contentToDelete = array();

        foreach ( $this->locationToDelete as $location )
        {
            try
            {
                $this->service->delete( $location );
            }
            catch ( NotFound $e )
            {
            }
        }

        $this->locationToDelete = array();
        $this->insertedLocations = array();

        parent::tearDown();
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
        $vo = $this->service->load( 2 )->getState( 'properties' );

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
        self::assertInstanceOf( 'ezp\\Content\\Location\\Proxy', $parent, 'Parent location must be a valid Proxy object after init by service' );
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
     * @group locationService
     * @covers ezp\Content\Location\Service::load
     */
    public function testChildren()
    {
        $location = $this->service->load( 2 );
        self::assertInstanceOf( 'ezp\\Content\\Location', $location );
        self::assertEquals( 2, count( $location->children ) );
    }

    /**
     * @expectedException \ezp\Content\Location\Exception\NotFound
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
        $remoteId = md5( microtime() );
        $parent = $this->service->load( 2 );
        $location = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $location->setParent( $parent );
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
        // @todo: Fix this when name is correctly handled
        // self::assertSame( 'test', $location->pathIdentificationString );

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
     * Test location creation
     * @covers ezp\Content\Location\Service::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $remoteId = md5( microtime() );
        $parent = $this->topLocation;
        $location = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $location->setParent( $parent );
        $location->remoteId = $remoteId;
        $location->sortField = Location::SORT_FIELD_PRIORITY;
        $location->sortOrder = Location::SORT_ORDER_DESC;
        $location->priority = 100;

        $this->service->create( $location );
    }

    /**
     * When creating a location, parent location is mandatory
     *
     * @group locationService
     * @covers ezp\Content\Location\Service::create
     * @expectedException \ezp\Base\Exception\Logic
     */
    public function testCreateNoParent()
    {
        $location = new ConcreteLocation( new ProxyContent( 1, $this->repository->getContentService() ) );
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
        $locationForTestContent = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $locationForTestContent->setParent( $this->topLocation );
        $locationForTestContent = $this->service->create( $locationForTestContent );
        $this->locationToDelete[] = $locationForTestContent;

        $hiddenLocation = $this->service->hide( $this->topLocation );
        self::assertInstanceOf( 'ezp\\Content\\Location', $hiddenLocation );
        self::assertTrue( $hiddenLocation->hidden );
        self::assertTrue( $locationForTestContent->invisible );
        unset( $locationForTestContent );
        self::assertGreaterThanOrEqual( $time, $this->service->load( 2 )->modifiedSubLocation );

        // Try to create a new location under a hidden one.
        // Newly created location should be invisible
        $location = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $location->setParent( $this->topLocation );
        self::assertTrue( $this->service->create( $location )->invisible );
        $this->locationToDelete[] = $location;

        // Create a new location under an invisible one
        // New location should also be invisible
        $anotherLocation4AnotherContent = new ConcreteLocation( new ProxyContent( 1, $this->repository->getContentService() ) );
        $anotherLocation4AnotherContent->setParent( $location );
        self::assertTrue( $this->service->create( $anotherLocation4AnotherContent )->invisible );
        $this->locationToDelete[] = $anotherLocation4AnotherContent;
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::hide
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testHideForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $this->service->hide( $this->topLocation );
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
        $locationShouldStayHidden = new ConcreteLocation( new ProxyContent( 1, $this->repository->getContentService() ) );
        $locationShouldStayHidden->setParent( $this->location );
        $this->service->create( $locationShouldStayHidden );
        $this->locationToDelete[] = $locationShouldStayHidden;
        $this->service->hide( $locationShouldStayHidden );

        // Create again a new location, under the last hidden one
        $locationShouldStayInvisible = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $locationShouldStayInvisible->setParent( $locationShouldStayHidden );
        $locationShouldStayInvisible = $this->service->create( $locationShouldStayInvisible );

        // Now test
        $parentMadeVisible = $this->service->unhide( $this->topLocation );
        self::assertInstanceOf( 'ezp\\Content\\Location', $parentMadeVisible );
        self::assertFalse( $this->location->invisible );
        self::assertFalse( $this->location->hidden );
        self::assertTrue(
            $locationShouldStayHidden->hidden && $locationShouldStayHidden->invisible,
            "A hidden location should not be made visible by superior location"
        );
        self::assertTrue( $locationShouldStayInvisible->invisible );
        self::assertGreaterThanOrEqual( $time, $this->service->load( $this->topLocation->id )->modifiedSubLocation );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::unhide
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUnHideForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $this->service->unhide( $this->topLocation );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::swap
     */
    public function testSwap()
    {
        $topContentId = $this->topLocation->contentId;
        $topContentName = $this->topLocation->getContent()->getCurrentVersion()->name;
        $topLocationId = $this->topLocation->id;
        $contentId = $this->location->contentId;
        $contentName = $this->location->getContent()->getCurrentVersion()->name;
        $locationId = $this->location->id;

        $this->service->swap( $this->topLocation, $this->location );

        self::assertSame( $topContentId, $this->location->contentId );
        self::assertSame( $topContentName, $this->location->getContent()->getCurrentVersion()->name );
        self::assertSame( $contentId, $this->topLocation->contentId );
        self::assertSame( $contentName, $this->topLocation->getContent()->getCurrentVersion()->name );
        self::assertSame( $topLocationId, $this->topLocation->id, 'Swapped locations keep same Ids' );
        self::assertSame( $locationId, $this->location->id, 'Swapped locations keep same Ids' );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::swap
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testSwapForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $this->service->swap( $this->topLocation, $this->location );
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
     * @covers ezp\Content\Location\Service::update
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $this->service->update( $this->location );
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::move
     */
    public function testMove()
    {
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
        foreach ( array_slice( $this->insertedLocations, $startIndex + 1 ) as $key => $location )
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
     * @covers \ezp\Content\Location\Service::delete
     */
    public function testDelete()
    {
        $startIndex = 5;
        $this->service->delete( $this->insertedLocations[$startIndex] );

        foreach ( array_slice( $this->insertedLocations, $startIndex ) as $key => $location )
        {
            try
            {
                $this->service->load( $location->id );
                $this->fail( "Location #{$location->id} has not been properly removed" );
            }
            catch ( NotFound $e )
            {
            }

            try
            {
                $this->repository->getContentService()->load( $location->contentId );
                $this->fail( "Content #{$location->contentId} has not been properly removed" );
            }
            catch ( NotFound $e )
            {
            }
        }

        // Create a secondary location for one the inserted locations
        // Delete the previous one and check if only location has been removed
        $startIndex--;
        $newLocation = new ConcreteLocation( new ProxyContent( $this->insertedLocations[$startIndex]->contentId, $this->repository->getContentService() ) );
        $newLocation->setParent( $this->topLocation );
        $newLocation = $this->service->create( $newLocation );
        $this->service->delete( $this->insertedLocations[$startIndex] );
        // Reload location from backend
        $newLocation = $this->service->load( $newLocation->id );
        self::assertSame( $newLocation->id, $newLocation->mainLocationId );
    }

    /**
     * @group locationService
     * @covers ezp\Content\Location\Service::delete
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        $this->repository->setUser( $this->anonymous );
        $this->service->delete( $this->location );
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::assignSection
     */
    public function testAssignSection()
    {
        $startIndex = 5;

        // Create the new section
        $section = new ConcreteSection;
        $section->identifier = 'myNewSection';
        $section->name = 'My new Section';
        $this->repository->getSectionService()->create( $section );

        // Assign the section to subtree
        $this->service->assignSection( $this->insertedLocations[$startIndex], $section );

        foreach ( array_slice( $this->insertedLocations, $startIndex ) as $location )
        {
            $content = $this->repository->getContentService()->load( $location->contentId );
            self::assertSame( $section->id, $content->sectionId );
        }
    }

    /**
     * Tests the copySubtree operation
     *
     * @group locationService
     * @covers \ezp\Content\Location\Service::copySubtree
     */
    public function testCopySubtree()
    {
        $newSubtree = $this->service->copySubtree( $this->insertedLocations[5], $this->topLocation );

        // @todo Need to check the subtree is correctly copied but since we have no
        // easy way to get children locations, postponing that.
        $this->markTestIncomplete();
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::copySubtree
     * @expectedException \ezp\Content\Location\Exception\NotFound
     */
    public function testCopySubtreeInvalidSource()
    {
        $newSubtree = $this->service->copySubtree(
            new ConcreteLocation( new ProxyContent( 1, $this->repository->getContentService() ) ),
            $this->topLocation
        );
    }

    /**
     * @group locationService
     * @covers \ezp\Content\Location\Service::copySubtree
     * @expectedException \ezp\Content\Location\Exception\NotFound
     */
    public function testCopySubtreeInvalidTarget()
    {
        $newSubtree = $this->service->copySubtree(
            $this->insertedLocations[5],
            new ConcreteLocation( new ProxyContent( 1, $this->repository->getContentService() ) )
        );
    }
}
