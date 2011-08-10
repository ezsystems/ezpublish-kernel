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
    \ReflectionObject,
    ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Content\Location,
    ezp\Content\Proxy,
    ezp\Content\ContainerProperty;

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
        // Removing default objects as well as those created by tests
        foreach ( $this->contentToDelete as $content )
        {
            $this->contentHandler->delete( $content->id );
        }

        foreach ( $this->locationToDelete as $location )
        {
            $this->locationHandler->delete( $location->id );
        }
        parent::tearDown();
    }

    /**
     * This test assures that domain object is properly built with value object
     * returned by repository handler
     *
     * @group locationService
     * @covers \ezp\Content\Location\Service::buildDomainObject
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

        $containerProperty = $do->containerProperties[0];
        self::assertInstanceOf( 'ezp\\Content\\ContainerProperty' , $containerProperty );
        self::assertEquals( $containerProperty->locationId, $vo->id );
        self::assertEquals( $containerProperty->sortField, $vo->sortField );
        self::assertEquals( $containerProperty->sortOrder, $vo->sortOrder );
        self::assertSame( $do, $containerProperty->location );
    }

    /**
     * Try to build Location domain object from not valid value object
     *
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     * @group locationService
     * @covers \ezp\Content\Location\Service::buildDomainObject
     */
    public function testBuildDomainObjectNotFromLocationVo()
    {
        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'buildDomainObject' );
        $refMethod->setAccessible( true );
        $do = $refMethod->invoke( $this->service, new Content );
    }

    /**
     * @group locationService
     */
    public function testLoad()
    {
        self::assertInstanceOf( 'ezp\\Content\\Location', $this->service->load( 2 ) );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group locationService
     */
    public function testLoadNonExistent()
    {
        $do = $this->service->load( 0 );
    }

    /**
     * Test location creation
     * @group locationService
     */
    public function testCreate()
    {
        $remoteId = md5(microtime());
        $parent = $this->service->load( 2 );
        $location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $location->parent = $parent;
        $location->remoteId = $remoteId;
        $location->sortField = ContainerProperty::SORT_FIELD_PRIORITY;
        $location->sortOrder = ContainerProperty::SORT_ORDER_DESC;
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

        // Expected depth should be number of locations in pathString - 1 (first level doesn't count)
        $expectedDepth = count( explode( '/', substr( $location->pathString, 1, -1 ) ) ) - 1;
        self::assertequals( $expectedDepth, $location->depth );
        self::assertEquals( $parent->depth + 1, $location->depth );
    }

    /**
     * When creating a location, parent location is mandatory
     * @expectedException \ezp\Base\Exception\Logic
     * @group locationService
     */
    public function testCreateNoParent()
    {
        $location = new Location( new Proxy( $this->repository->getContentService(), 1 ) );
        $do = $this->service->create( $location );
    }

    /**
     * @group locationService
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
}
