<?php
/**
 * File contains: ezp\Content\Tests\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Tests\BaseServiceTest,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Location,
    \ReflectionObject;

/**
 * Test case for Content service
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Service
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getContentService();
    }

    /**
     * This test assures that domain object is properly built with value object
     * returned by repository handler
     *
     * @group contentService
     * @covers \ezp\Content\Service::buildDomainObject
     */
    public function testBuildDomainObject()
    {
        $vo = $this->repositoryHandler->contentHandler()->load( 1 );

        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( "buildDomainObject" );
        $refMethod->setAccessible( true );
        $do = $refMethod->invoke( $this->service, $vo );

        $refDo = new ReflectionObject( $do );
        $doRefProperties = $refDo->getProperty( "properties" );
        $doRefProperties->setAccessible( true );
        self::assertSame( $vo, $doRefProperties->getValue( $do ) );

        $refSection = $refDo->getProperty( "section" );
        $refSection->setAccessible( true );
        $section = $refSection->getValue( $do );
        self::assertInstanceOf( "ezp\\Content\\Proxy", $section, "Section must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->sectionId, $section->id );

        $refContentType = $refDo->getProperty( "contentType" );
        $refContentType->setAccessible( true );
        $contentType = $refContentType->getValue( $do );
        self::assertInstanceOf( "ezp\\Content\\Proxy", $contentType, "Content Type must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->typeId, $contentType->id );

        self::assertEquals( 14, $do->ownerId, "Owner ID must be the one of Administrator" );
        self::assertEquals( 1, $do->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $do->id, "Content ID not correctly set" );
        self::assertInstanceOf( "ezp\\Content\\Type", $do->contentType, "Content type not correctly set" );
        self::assertEquals( 1, $do->contentType->id, "Content type retrieved is not the good one" );
        self::assertEquals( "eZ Publish", $do->name, "Content name not correctly set" );
    }

    /**
     * Try to build Content domain object from not valid value object
     *
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     * @group contentService
     * @covers \ezp\Content\Service::buildDomainObject
     */
    public function testBuildDomainObjectNotFromContentVo()
    {
        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( "buildDomainObject" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $this->service, new Location );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::load
     */
    public function testLoad()
    {
        self::assertInstanceOf( "ezp\\Content", $this->service->load( 1 ) );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::load
     */
    public function testLoadNonExistent()
    {
        $this->service->load( 0 );
    }
}
