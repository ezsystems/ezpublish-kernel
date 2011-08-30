<?php
/**
 * File contains: ezp\Content\Tests\Location\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Location\Trash\Service,
    ezp\Content\Location\Service as LocationService,
    ezp\Base\Exception\NotFound,
    \ReflectionObject,
    ezp\Content,
    ezp\Content\Type,
    ezp\Content\Location,
    ezp\Content\Location\Trashed,
    ezp\Base\Proxy,
    ezp\Content\Section;

/**
 * Test case for Location service
 */
class ServiceTest extends Base
{
    /**
     * @var \ezp\Content\Location\Trash\Service
     */
    protected $service;

    /**
     * @var type \ezp\Content\Location\Service
     */
    protected $locationService;

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
     * @var \ezp\Persistence\Content\Location\Trash\Handler
     */
    protected $trashHandler;

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
        $this->locationService = $this->repository->getLocationService();
        $this->service = $this->repository->getTrashService();

        $type = $this->repository->getContentTypeService()->load( 1 );
        $section = $this->repository->getSectionService()->load( 1 );
        $content = new Content( $type );
        $content->name = "test";
        $content->ownerId = 14;
        $content->section = $section;
        $content->fields['name'] = 'Welcome';

        $this->content = $this->repository->getContentService()->create( $content );
        $this->contentToDelete[] = $this->content;

        // Now creating location for content
        $this->topLocation = $this->locationService->load( 2 );
        $this->location = new Location( new Proxy( $this->repository->getContentService(), $this->content->id ) );
        $this->location->parent = $this->topLocation;
        $this->location = $this->locationService->create( $this->location );
        $this->locationToDelete[] = $this->location;

        $parentId = $this->topLocation->id;
        for ( $i = 0; $i < 10; ++$i )
        {

            $content = new Content( $type );
            $content->name = "foo$i";
            $content->ownerId = 14;
            $content->section = $section;
            $content->fields['name'] = "bar$i";

            $content = $this->repository->getContentService()->create( $content );
            $this->contentToDelete[] = $content;

            $location = new Location( $content );
            $location->parent = $this->locationService->load( $parentId );
            $location = $this->locationService->create( $location );
            $this->locationToDelete[] = $location;
            $this->insertedLocations[] = $location;
            $parentId = $location->id;
        }
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
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
                $this->locationService->delete( $location );
            }
            catch ( NotFound $e )
            {
            }
        }

        try
        {
            $this->service->emptyTrash();
        }
        catch ( NotFound $e )
        {
        }

        $this->locationToDelete = array();
        $this->insertedLocations = array();
        $this->trashedLocationToDelete = array();

        parent::tearDown();
    }

    private function compareTrashedAndLocation( Trashed $trashed, Location $location )
    {
        $vo = $trashed->getState( 'properties' );
        // Trashed VO properties should be the same than original location properties
        foreach ( $location->getState( 'properties' ) as $property => $value )
        {
            if ( $property == 'id' )
            {
                self::assertSame( $value, $vo->locationId );
            }
            else
            {
                self::assertSame( $value, $vo->$property );
            }
        }
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::trash
     */
    public function testTrashOne()
    {
        $trashed = $this->service->trash( $this->location );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $trashed );

        $this->compareTrashedAndLocation( $trashed, $this->location );
        $this->setExpectedException( 'ezp\\Base\\Exception\\NotFound' );
        $this->locationService->load( $trashed->locationId );
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::trash
     */
    public function testTrashSubtree()
    {
        $topTrashed = $this->service->trash( $this->insertedLocations[0] );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed' , $topTrashed );

        foreach ( $this->insertedLocations as $location )
        {
            $trashedLocation = $this->service->loadByLocationId( $location->id );
            self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $trashedLocation );
            $this->compareTrashedAndLocation( $trashedLocation, $location );

            try
            {
                $this->locationService->load( $trashedLocation->locationId );
                $this->fail( 'A trashed location has to be removed from tree and placed into the trash' );
            }
            catch( NotFound $e )
            {
            }
        }
    }

    /**
     * This test ensures that domain object is properly built with value object
     * returned by repository handler
     *
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::buildDomainObject
     */
    public function testBuildDomainObject()
    {
        $vo = $this->service->trash( $this->location )->getState( 'properties' );

        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'buildDomainObject' );
        $refMethod->setAccessible( true );
        $do = $refMethod->invoke( $this->service, $vo );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $do );
        $this->compareTrashedAndLocation( $do, $this->location );

        $refDo = new ReflectionObject( $do );
        $doRefProperties = $refDo->getProperty( 'properties' );
        $doRefProperties->setAccessible( true );
        $doProperties = $doRefProperties->getValue( $do );
        self::assertSame( $vo, $doProperties );

        $refParent = $refDo->getProperty( 'parent' );
        $refParent->setAccessible( true );
        $parent = $refParent->getValue( $do );
        self::assertInstanceOf( 'ezp\\Base\\Proxy', $parent, 'Parent location must be a valid Proxy object after init by service' );
        self::assertEquals( $vo->parentId, $parent->id );

        $refContent = $refDo->getProperty( 'content' );
        $refContent->setAccessible( true );
        $content = $refContent->getValue( $do );
        self::assertInstanceOf( 'ezp\\Base\\Proxy', $content, 'Content must be a valid Proxy object after init by service' );
        self::assertEquals( $vo->contentId, $content->id );

        self::assertEquals( $do->sortField, $vo->sortField );
        self::assertEquals( $do->sortOrder, $vo->sortOrder );
    }


    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::load
     */
    public function testLoad()
    {
        $trashed = $this->service->trash( $this->location );
        $trashedLoaded = $this->service->load( $trashed->id );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $trashedLoaded );
        $this->compareTrashedAndLocation( $trashedLoaded, $this->location );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::load
     */
    public function testLoadNonExistent()
    {
        $do = $this->service->load( 0 );
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::load
     */
    public function testLoadByLocationId()
    {
        $trashed = $this->service->trash( $this->location );
        $trashedLoaded = $this->service->loadByLocationId( $this->location->id );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $trashedLoaded );
        $this->compareTrashedAndLocation( $trashedLoaded, $this->location );
    }

    /**
     * @group trashService
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers ezp\Content\Location\Trash\Service::load
     */
    public function testLoadByLocationIdNonExistent()
    {
        $this->service->loadByLocationId( 0 );
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::emptyTrash
     */
    public function testEmptyTrash()
    {
        $trashed = $this->service->trash( $this->insertedLocations[0] );

        $this->service->emptyTrash();
        foreach ( $this->insertedLocations as $location )
        {
            try
            {
                $this->service->loadByLocationId( $location->id );
                $this->fail( 'Emptying the trash should remove ALL trashed locations' );
            }
            catch ( NotFound $e )
            {
            }
        }
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::emptyOne
     */
    public function testEmptyOne()
    {
        $this->markTestIncomplete();
    }
}

