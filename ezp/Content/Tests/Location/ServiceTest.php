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

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getLocationService();
        $this->locationHandler = $this->repositoryHandler->locationHandler();

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

        $this->content = $this->repositoryHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();
        // Removing default objects as well as those created by tests
        foreach ( $this->contentToDelete as $content )
        {
            $contentHandler->delete( $content->id );
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
     * @covers \ezp\Content\Location\Service::testBuildDomainObject
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
     * @covers \ezp\Content\Location\Service::testBuildDomainObject
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
        $parent = $this->service->load( 2 );
        $time = time();
        // Setup a location for test content and delete local variable
        $locationForTestContent = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $locationForTestContent->parent = $parent;
        $locationForTestContent = $this->service->create( $locationForTestContent );
        $this->locationToDelete[] = $locationForTestContent;

        $hiddenLocation = $this->service->hide( $parent );
        self::assertInstanceOf( 'ezp\\Content\\Location' , $hiddenLocation );
        self::assertTrue( $hiddenLocation->hidden );
        self::assertTrue( $locationForTestContent->invisible );
        unset( $locationForTestContent );
        self::assertGreaterThanOrEqual( $time, $this->locationHandler->load( 2 )->modifiedSubLocation );

        // Try to create a new location under a hidden one.
        // Newly created location should be invisible
        $location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $location->parent = $parent;
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
        // First set up some locations to test
        $parent = $this->service->load( 2 );
        $time = time();
        $locationForTestContent = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $locationForTestContent->parent = $parent;
        $locationForTestContent = $this->service->create( $locationForTestContent );
        $this->locationToDelete[] = $locationForTestContent;

        $location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $location->parent = $parent;
        $this->service->create( $location );
        $this->locationToDelete[] = $location;

        // Hide the main location
        $hiddenLocation = $this->service->hide( $parent );

        // Create a new location that will be hidden
        $locationShouldStayHidden = new Location( new Proxy( $this->repository->getContentService(), 1 ) );
        $locationShouldStayHidden->parent = $location;
        $this->service->create( $locationShouldStayHidden );
        $this->locationToDelete[] = $locationShouldStayHidden;
        $this->service->hide( $locationShouldStayHidden );

        // Create again a new location, under the last hidden one
        $locationShouldStayInvisible = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $locationShouldStayInvisible->parent = $locationShouldStayHidden;
        $locationShouldStayInvisible = $this->service->create( $locationShouldStayInvisible );

        // Now test
        $parentMadeVisible = $this->service->unhide( $parent );
        self::assertInstanceOf( 'ezp\\Content\\Location' , $parentMadeVisible );
        self::assertFalse( $location->invisible );
        self::assertFalse( $location->hidden );
        self::assertTrue( $locationShouldStayHidden->hidden && $locationShouldStayHidden->invisible,
                          'A hidden location should not be made visible by superior location' );
        self::assertTrue( $locationShouldStayInvisible->invisible );
        self::assertGreaterThanOrEqual( $time, $this->locationHandler->load( $parent->parentId )->modifiedSubLocation );
    }
}
