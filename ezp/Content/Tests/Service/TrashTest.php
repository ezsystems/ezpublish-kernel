<?php
/**
 * File contains: ezp\Content\Tests\Service\TrashTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Location,
    ezp\Content\Location\Trash\Service,
    ezp\Content\Location\Service as LocationService,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Content\Location\Collection,
    ezp\Content\Location\Trashed,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Type,
    ezp\Content\Query,
    ezp\Content\Proxy as ProxyContent,
    ezp\Content\Query\Builder as QueryBuilder,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content\Location\Trashed as TrashedValue,
    ezp\User\Proxy as ProxyUser,
    ReflectionObject;

/**
 * Test case for Location service
 */
class TrashTest extends Base
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
        $administrator = new ProxyUser( 14, $this->repository->getUserService() );
        $this->repository->setUser( $administrator );// "Login" admin

        $this->locationService = $this->repository->getLocationService();
        $this->service = $this->repository->getTrashService();


        $type = $this->repository->getContentTypeService()->load( 1 );
        $section = $this->repository->getSectionService()->load( 1 );
        $content = new ConcreteContent( $type, $administrator );
        $content->name = "test";
        $content->setSection( $section );
        $fields = $content->getFields();
        $fields['name'] = 'Welcome';

        $this->content = $this->repository->getContentService()->create( $content );
        $this->contentToDelete[] = $this->content;

        // Now creating location for content
        $this->topLocation = $this->locationService->load( 2 );
        $this->location = new ConcreteLocation( new ProxyContent( $this->content->id, $this->repository->getContentService() ) );
        $this->location->setParent( $this->topLocation );
        $this->location = $this->locationService->create( $this->location );
        $this->locationToDelete[] = $this->location;

        $parent = $this->topLocation;
        for ( $i = 0; $i < 10; ++$i )
        {
            $content = new ConcreteContent( $type, $administrator );
            $content->name = "foo$i";
            $content->setSection( $section );
            $fields = $content->getFields();
            $fields['name'] = "bar$i";

            $content = $this->repository->getContentService()->create( $content );
            $this->contentToDelete[] = $content;

            $location = new ConcreteLocation( $content );
            $location->setParent( $parent );
            $location = $this->locationService->create( $location );
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
            if ( $property != 'modifiedSubLocation' )
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
        $this->locationService->load( $trashed->id );
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
            $trashedLocation = $this->service->load( $location->id );
            self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $trashedLocation );
            $this->compareTrashedAndLocation( $trashedLocation, $location );

            try
            {
                $this->locationService->load( $trashedLocation->id );
                $this->fail( 'A trashed location has to be removed from tree and placed into the trash' );
            }
            catch ( NotFound $e )
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
     * @expectedException \ezp\Content\Location\Trash\Exception\NotFound
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::load
     */
    public function testLoadNonExistent()
    {
        $do = $this->service->load( 0 );
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
                $this->service->load( $location->id );
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
        $trashedTop = $this->service->trash( $this->insertedLocations[0] );
        $trashed = $this->service->trash( $this->location );

        $this->service->emptyOne( $trashed );
        // Check that trashed inserted locations has not been emptied
        foreach ( $this->insertedLocations as $location )
        {
            self::assertInstanceOf( 'ezp\\Content\\Location\\Trashed', $this->service->load( $location->id ) );
        }

        // We should not be able to reload emptied trashed location
        $this->setExpectedException( 'ezp\\Base\\Exception\\NotFound' );
        $this->service->load( $this->location->id );
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::untrash
     */
    public function testUntrashOriginalLocation()
    {
        $trashed = $this->service->trash( $this->location );
        $restored = $this->service->untrash( $trashed );
        self::assertInstanceOf( 'ezp\\Content\\Location', $restored );

        $restoredVo = $restored->getState( 'properties' );
        foreach ( $this->location->getState( 'properties' ) as $property => $value )
        {
            switch ( $property )
            {
                case 'id':
                    self::assertGreaterThan( $value, $restoredVo->$property );
                    break;

                case 'remoteId':
                case 'contentId':
                case 'parentId':
                case 'priority':
                case 'hidden':
                case 'invisible':
                case 'sortField':
                case 'sortOrder':
                    self::assertSame( $value, $restoredVo->$property, "$property on restored location must be the same than old location" );
                    break;

                case 'pathString':
                    self::assertSame( "{$this->location->parent->pathString}{$restoredVo->id}/", $restoredVo->pathString );
                    break;

                case 'depth':
                    self::assertSame( $this->location->parent->depth + 1, $restoredVo->depth );
                    break;

                case 'mainLocationId':
                    self::assertSame( $restoredVo->mainLocationId, $restoredVo->id );
                    break;
            }
        }
    }

    /**
     * Trash a location, then its parent.
     * Trying to restore the location should throw a ParentNotFound exception
     *
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::untrash
     * @expectedException \ezp\Content\Location\Exception\ParentNotFound
     */
    public function testUntrashOriginalLocationUnavailable()
    {
        $trashed = $this->service->trash( $this->insertedLocations[1] );
        $parentTrashed = $this->service->trash( $this->insertedLocations[0] );
        $restored = $this->service->untrash( $trashed );
    }

    /**
     * @group trashService
     * @covers ezp\Content\Location\Trash\Service::untrash
     */
    public function testUntrashDifferentLocation()
    {
        $trashed = $this->service->trash( $this->location );
        $restored = $this->service->untrash( $trashed, $this->insertedLocations[0] );
        self::assertInstanceOf( 'ezp\\Content\\Location', $restored );

        $restoredVo = $restored->getState( 'properties' );
        foreach ( $this->location->getState( 'properties' ) as $property => $value )
        {
            switch ( $property )
            {
                case 'id':
                    self::assertGreaterThan( $value, $restoredVo->$property );
                    break;

                case 'parentId':
                    self::assertSame(
                        $this->insertedLocations[0]->id,
                        $restoredVo->$property,
                        "Restored location's parentId should equal to the new one (#{$this->insertedLocations[0]->id})"
                    );
                    break;

                case 'remoteId':
                case 'contentId':
                case 'priority':
                case 'hidden':
                case 'invisible':
                case 'sortField':
                case 'sortOrder':
                    self::assertSame( $value, $restoredVo->$property, "$property on restored location must be the same than old location" );
                    break;

                case 'pathString':
                    self::assertSame( "{$this->insertedLocations[0]->pathString}{$restoredVo->id}/", $restoredVo->pathString );
                    break;

                case 'depth':
                    self::assertSame( $this->insertedLocations[0]->depth + 1, $restoredVo->depth );
                    break;

                case 'mainLocationId':
                    self::assertSame( $restoredVo->mainLocationId, $restoredVo->id );
                    break;
            }
        }
    }

    /**
     * @group trashService
     * @covers \ezp\Content\Location\Trash\Service::untrash
     * @expectedException \ezp\Content\Location\Exception\ParentNotFound
     */
    public function testUntrashDifferentUnavailableLocation()
    {
        // Remove future parent
        $this->locationService->delete( $this->insertedLocations[0] );
        $trashed = $this->service->trash( $this->location );
        $restored = $this->service->untrash( $trashed, $this->insertedLocations[0] );
    }

    /**
     * Returns mock object for trash handler and inject it in repository handler
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockForTrash()
    {
        $refRepository = new ReflectionObject( $this->repository );
        $refHandlerProp = $refRepository->getProperty( 'handler' );
        $refHandlerProp->setAccessible( true );
        $repositoryHandler = $refHandlerProp->getValue( $this->repository );
        $refHandler = new ReflectionObject( $repositoryHandler );
        $refBackend = $refHandler->getProperty( 'backend' );
        $refBackend->setAccessible( true );
        $trashHandler = $this->getMockBuilder( 'ezp\\Persistence\\Storage\\InMemory\\TrashHandler' )
                                    ->setConstructorArgs(
                                        array(
                                            $repositoryHandler,
                                            $refBackend->getValue( $repositoryHandler )
                                        )
                                    )
                                    ->getMock();
        $refServiceHandlersProp = $refHandler->getProperty( 'serviceHandlers' );
        $refServiceHandlersProp->setAccessible( true );
        $refServiceHandlersProp->setValue(
            $repositoryHandler,
            array(
                'ezp\\Persistence\\Storage\\InMemory\\TrashHandler' => $trashHandler
            ) + $refServiceHandlersProp->getValue( $repositoryHandler )
        );

        return $trashHandler;
    }

    /**
     * @group trashService
     * @covers \ezp\Content\Location\Trash\Service::getList
     */
    public function testGetList()
    {
        $trashHandler = $this->getMockForTrash();
        $limit = 7;
        $expectedResult = array();
        for ( $i = 0; $i < $limit; $i++ )
        {
            $expectedResult[] = new TrashedValue(
                array(
                    'id' => $i + 1,
                    'contentId' => $i + 1,
                )
            );
        }

        $qb = new QueryBuilder;
        $qb->addCriteria(
            $qb->fullText->like( 'foo*' )
        )
        ->addSortClause(
            $qb->sort->field( 'folder', 'name', Query::SORT_ASC ),
            $qb->sort->dateCreated( Query::SORT_DESC )
        )
        ->setOffset( 3 )->setLimit( $limit );
        $query = $qb->getQuery();

        $trashHandler->expects( $this->once() )
                     ->method( 'listTrashed' )
                     ->with(
                         $query->criterion,
                         $query->offset,
                         $query->limit,
                         $query->sortClauses
                     )
                     ->will( $this->returnValue( $expectedResult ) );

        $result = $this->service->getList( $query );
        self::assertInstanceOf( 'ezp\\Content\\Location\\Collection' , $result );
        self::assertEquals( $limit, count( $result ) );
    }
}
