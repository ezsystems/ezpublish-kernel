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
        $content->name = "New object";
        $content->ownerId = 10;
        $content->section = $section;

        $content = $this->service->create( $content );
        // @todo: Deal with field value when that is ready for manipulation
        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( "New object", $content->name, "Name not correctly set" );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_DRAFT, $content->status, "Status not correctly set" );
        self::assertEquals( 1, count( $content->locations ), "Location count is wrong" );
        self::assertEquals( $content->locations[0]->id, $content->locations[0]->mainLocationId, "Main Location id is not correct" );
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
        self::assertEquals( "eZ Publish", $content->name, "Name not correctly set" );
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
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[0] );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[1] );

        $version = new Version( $content );
        $version->setState(
            array(
                "properties" => new VersionValue(
                    array(
                        "id" => 1,
                        "contentId" => 1,
                        "versionNo" => 1,
                        "modified" => 1310792400,
                        "created" => 1310792400,
                        "creatorId" => 14,
                        "state" => 1,
                    )
                ),
                "fields" => array(),
            )
        );
        $this->assertEquals( $version, $content->versions[0] );

        $version = new Version( $content );
        $version->setState(
            array(
                "properties" => new VersionValue(
                    array(
                        "id" => 2,
                        "contentId" => 1,
                        "versionNo" => 2,
                        "modified" => 1310793400,
                        "created" => 1310793400,
                        "creatorId" => 14,
                        "state" => 0,
                    )
                ),
                "fields" => array(),
            )
        );
        $this->assertEquals( $version, $content->versions[1] );
    }

    /**
     * Test the Content Service listVersions operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::listVersions
     */
    public function testListVersions()
    {
        $content = $this->service->load( 1 );
        $versions = $this->service->listVersions( $content );
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
        $content = new Content( new Type );
        $content->getState( "properties" )->id = 999;
        $versions = $this->service->listVersions( $content );
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
