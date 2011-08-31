<?php
/**
 * File contains: ezp\Content\Tests\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content,
    ezp\Content\Location,
    ezp\Content\Type,
    ezp\Content\Version,
    ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content as ContentValue,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\Content\Version as VersionValue,
    ezp\Persistence\Content\Criterion\ContentId,
    \ReflectionObject;

/**
 * Test case for Content service
 */
class ContentTest extends BaseServiceTest
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
        $vo = $this->service->load( 1 )->getState( 'properties' );

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
        self::assertInstanceOf( "ezp\\Base\\Proxy", $section, "Section must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->sectionId, $section->id );

        $refContentType = $refDo->getProperty( "contentType" );
        $refContentType->setAccessible( true );
        $contentType = $refContentType->getValue( $do );
        self::assertInstanceOf( "ezp\\Base\\Proxy", $contentType, "Content Type must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->typeId, $contentType->id );

        self::assertEquals( 14, $do->ownerId, "Owner ID must be the one of Administrator" );
        self::assertEquals( 1, $do->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $do->id, "Content ID not correctly set" );
        self::assertInstanceOf( "ezp\\Content\\Type", $do->contentType, "Content type not correctly set" );
        self::assertEquals( 1, $do->contentType->id, "Content type retrieved is not the good one" );
        self::assertEquals( array( "eng-GB" => "eZ Publish" ), $do->name, "Content name not correctly set" );
    }

    /**
     * Try to build Content domain object from not valid value object
     *
     * @expectedException \PHPUnit_Framework_Error
     * @group contentService
     * @covers \ezp\Content\Service::buildDomainObject
     */
    public function testBuildDomainObjectNotFromContentVo()
    {
        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( "buildDomainObject" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $this->service, new LocationValue );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::create
     */
    public function testCreate()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 1 );
        $content = new Content( $type );
        $content->addParent( $location );
        $content->name = array( "eng-GB" => "New object" );
        $content->ownerId = 10;
        $content->section = $section;

        $content = $this->service->create( $content );
        // @todo: Deal with field value when that is ready for manipulation
        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( array( "eng-GB" => "New object" ), $content->name, "Name not correctly set" );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_DRAFT, $content->status, "Status not correctly set" );
        self::assertEquals( 1, count( $content->locations ), "Location count is wrong" );
        self::assertEquals( $content->locations[0]->id, $content->locations[0]->mainLocationId, "Main Location id is not correct" );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::update
     */
    public function testUpdate()
    {
        // @todo Test with change to fields!

        $content = $this->service->load( 1 );
        $content->name = array( "eng-GB" => "New name");
        $content->ownerId = 10;
        $content = $this->service->update( $content );

        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( 1, $content->id, "ID not correctly set" );
        self::assertEquals( array( "eng-GB" => "New name" ), $content->name, "Name not correctly set" );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_DRAFT, $content->status, "Status not correctly set" );
    }

    /**
     * Test the Content Service load operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::load
     */
    public function testLoad()
    {
        $content = $this->service->load( 1 );
        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( 1, $content->id, "ID not correctly set" );
        self::assertEquals( array( "eng-GB" => "eZ Publish" ), $content->name, "Name not correctly set" );
        self::assertEquals( 14, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->sectionId, "Section ID not correctly set" );
    }

    /**
     * Test the getVersions() method after having loaded the content with the service
     *
     * @group contentService
     * @covers \ezp\Content\Service::load
     * @covers \ezp\Content::getVersions
     */
    public function testGetVersions()
    {
        $content = $this->service->load( 1 );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Type", $content->versions );
        $this->assertEquals( 2, count( $content->versions ) );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[1] );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[2] );

        $this->assertEquals( 1, $content->versions[1]->id );
        $this->assertEquals( 1, $content->versions[1]->contentId );
        $this->assertEquals( 1, $content->versions[1]->versionNo );
        $this->assertEquals( 1310792400, $content->versions[1]->modified );
        $this->assertEquals( 1310792400, $content->versions[1]->created );
        $this->assertEquals( 14, $content->versions[1]->creatorId );
        $this->assertEquals( Content::STATUS_PUBLISHED, $content->versions[1]->state );

        $this->assertEquals( 2, $content->versions[2]->id );
        $this->assertEquals( 1, $content->versions[1]->contentId );
        $this->assertEquals( 2, $content->versions[2]->versionNo );
        $this->assertEquals( 1310793400, $content->versions[2]->modified );
        $this->assertEquals( 1310793400, $content->versions[2]->created );
        $this->assertEquals( 14, $content->versions[2]->creatorId );
        $this->assertEquals( Content::STATUS_DRAFT, $content->versions[2]->state );
    }

    /**
     * Test the Content Service listVersions operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::listVersions
     */
    public function testListVersions()
    {
        $versions = $this->service->listVersions( 1 );
        $this->assertEquals( 2, count( $versions ) );
        $foundVersions = array();
        foreach ( $versions as $version )
        {
            $foundVersions[$version->id] = true;
            $this->assertEquals( 1, $version->contentId );
            $this->assertEquals( 14, $version->creatorId );
            $this->assertEquals( $version->id, $version->versionNo );

            if ( $version->id == 1 )
            {
                $this->assertEquals( 1310792400, $version->modified );
                $this->assertEquals( 1310792400, $version->created );
                $this->assertEquals( 1, $version->state );
            }
            else if ( $version->id == 2 )
            {
                $this->assertEquals( 1310793400, $version->modified );
                $this->assertEquals( 1310793400, $version->created );
                $this->assertEquals( 0, $version->state );
            }
        }
        $this->assertEquals( array( 1 => true, 2 => true ), $foundVersions, "The versions returned is not correct" );
    }

    /**
     * Test the Content Service listVersions operation
     * with a wrong Content argument
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::listVersions
     */
    public function testListVersionsNotExisting()
    {
        $versions = $this->service->listVersions( 999 );
    }

    /**
     * Test the Content Service delete operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::delete
     */
    public function testDelete()
    {
        $content = $this->service->load( 1 );
        $locations = $content->locations;
        $this->service->delete( $content );
        $locationService = $this->repository->getLocationService();
        foreach ( $locations as $location )
        {
            try
            {
                $locationService->load( $location->id );
                $this->fail( "Location not correctly deleted while deleting Content" );
            }
            catch ( NotFound $e )
            {
            }
        }
    }

    /**
     * Test the Content Service delete operation
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::delete
     */
    public function testDeleteNotExisting()
    {
        $content = new Content( new Type );
        $content->getState( "properties" )->id = 999;
        $this->service->delete( $content );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::load
     */
    public function testLoadNotExisting()
    {
        $this->service->load( 0 );
    }
}
