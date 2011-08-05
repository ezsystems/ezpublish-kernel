<?php
/**
 * File contains: ezp\Content\Tests\Location\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Location;
use ezp\Content\Tests\ServiceTest as BaseServiceTest,
    ezp\Content\Location\Service,
    \ReflectionObject,
    ezp\Persistence\Content;

/**
 * Test case for Location service
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * @var ezp\Content\Location\Service
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getLocationService();
    }

    /**
     * This test assures that domain object is properly built with value object
     * returned by repository handler
     *
     * @group locationService
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
     * @expectedException ezp\Base\Exception\InvalidArgumentType
     * @group locationService
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
     * @expectedException ezp\Base\Exception\NotFound
     * @group locationService
     */
    public function testLoadNonExistent()
    {
        $do = $this->service->load( 0 );
    }
}
