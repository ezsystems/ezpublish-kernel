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

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getLocationService();

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

        $this->repositoryHandler->locationHandler()->delete( $locationId );
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
}
