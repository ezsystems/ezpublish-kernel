<?php
/**
 * File contains: ezp\Content\Tests\Service\ContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Relation,
    ezp\Content\Version,
    ezp\Content\Version\Concrete as ConcreteVersion,
    ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\Content\Type as TypeValue,
    ezp\User\Proxy as ProxyUser,
    ReflectionObject;

/**
 * Test case for Content service
 */
class ContentTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Service
     */
    protected $service;

    /**
     * @var \ezp\User
     */
    protected $anonymousUser;

    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getContentService();
        $this->anonymousUser = $this->repository->setUser(
            new ProxyUser( 14, $this->repository->getUserService() )
        );// "Login" admin

        $vo = new TypeValue(
            array(
                'id' => 1,
                'status' => TypeValue::STATUS_DEFINED
            )
        );
        $this->contentType = new ConcreteType();
        $this->contentType->setState(
            array( 'properties' => $vo )
        );
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
        self::assertInstanceOf( "ezp\\Content\\Section\\Proxy", $section, "Section must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->sectionId, $section->id );

        $refContentType = $refDo->getProperty( "contentType" );
        $refContentType->setAccessible( true );
        $contentType = $refContentType->getValue( $do );
        self::assertInstanceOf( "ezp\\Content\\Type\\Proxy", $contentType, "Content Type must be a valid Proxy object after init by service" );
        self::assertEquals( $vo->typeId, $contentType->id );

        self::assertEquals( 14, $do->ownerId, "Owner ID must be the one of Administrator" );
        self::assertEquals( 1, $do->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $do->id, "Content ID not correctly set" );
        self::assertInstanceOf( "ezp\\Content\\Type", $do->contentType, "Content type not correctly set" );
        self::assertEquals( 1, $do->contentType->id, "Content type retrieved is not the good one" );
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
     * This tests ensures that version domain object is properly built with version value object
     * returned by repository handler
     *
     * @group contentService
     * @covers \ezp\Content\Service::buildVersionDomainObject
     */
    public function testBuildVersionDomainObject()
    {
        $content = $this->service->load( 1 );
        $versionVo = $content->versions[1]->getState( 'properties' );

        $refService = new ReflectionObject( $this->service );
        $refMethod = $refService->getMethod( 'buildVersionDomainObject' );
        $refMethod->setAccessible( true );
        $do = $refMethod->invoke( $this->service, $content, $versionVo );
        self::assertSame( $versionVo, $do->getState( 'properties' ) );
        self::assertInstanceOf( 'ezp\\Base\\Collection', $do->getFields() );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::create
     */
    public function testCreate()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 2 );
        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );
        $content->setSection( $section );

        $content = $this->service->create( $content );
        // @todo: Deal with field value when that is ready for manipulation
        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 2, $content->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_DRAFT, $content->status, "Status not correctly set" );
        $locations = $content->getLocations();
        self::assertEquals( 1, count( $locations ), "Location count is wrong" );
        self::assertEquals( $locations[0]->id, $locations[0]->mainLocationId, "Main Location id is not correct" );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::create
     */
    public function testCreateInheritSection()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );

        $content = $this->service->create( $content );

        // @todo: Deal with field value when that is ready for manipulation
        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->sectionId, "Section ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_DRAFT, $content->status, "Status not correctly set" );
        $locations = $content->getLocations();
        self::assertEquals( 1, count( $locations ), "Location count is wrong" );
        self::assertEquals( $locations[0]->id, $locations[0]->mainLocationId, "Main Location id is not correct" );
        self::assertNotEquals( 0, $content->published );
        self::assertNotEquals( 0, $content->modified );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 2 );
        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );
        $content->setSection( $section );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->create( $content );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::update
     */
    public function testUpdate()
    {
        // @todo Test with change to fields!

        $content = $this->service->load( 1 );
        $content->setOwner( $this->anonymousUser );
        $content = $this->service->update( $content, $content->versions[1] );

        self::assertInstanceOf( "ezp\\Content", $content );
        self::assertEquals( 1, $content->id, "ID not correctly set" );
        self::assertEquals( 10, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->currentVersionNo, "currentVersionNo not correctly set" );
        self::assertEquals( Content::STATUS_PUBLISHED, $content->status, "Status not correctly set" );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::update
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        $content = $this->service->load( 1 );
        $content->setOwner( $this->anonymousUser );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->update( $content, $content->versions[1] );
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
        self::assertEquals( 14, $content->ownerId, "Owner ID not correctly set" );
        self::assertEquals( 1, $content->sectionId, "Section ID not correctly set" );
    }

    /**
     * Test the Content Service load operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::loadVersion
     * @covers \ezp\Content\Version\Concrete::getOwnerId
     * @covers \ezp\Content\Version\Concrete::getSectionId
     * @covers \ezp\Content\Version\Concrete::getTypeId
     * @covers \ezp\Content\Version\Concrete::getContentStatus
     */
    public function testLoadVersion()
    {
        $version = $this->service->loadVersion( 1 );
        self::assertInstanceOf( "ezp\\Content\\Version", $version );
        self::assertEquals( 1, $version->contentId, "Content ID not correctly set" );
        self::assertEquals( 1, $version->id, "ID not correctly set" );
        self::assertEquals( array( "eng-GB" => "eZ Publish" ), $version->name, "Name not correctly set" );
        self::assertEquals( 14, $version->getOwnerId(), "Owner ID not correctly set" );
        self::assertEquals( 1, $version->getSectionId(), "Section ID not correctly set" );
        self::assertEquals( 1, $version->getTypeId(), "Type ID not correctly set" );
        self::assertEquals( Content::STATUS_PUBLISHED, $version->getContentStatus(), "Content status not correctly set" );
    }

    /**
     * Test the Content Service load operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::loadVersion
     * @covers \ezp\Content\Version\Concrete::getOwnerId
     * @covers \ezp\Content\Version\Concrete::getSectionId
     * @covers \ezp\Content\Version\Concrete::getTypeId
     * @covers \ezp\Content\Version\Concrete::getContentStatus
     */
    public function testLoadVersionWithVersionNumber()
    {
        $version = $this->service->loadVersion( 1, 2 );
        self::assertInstanceOf( "ezp\\Content\\Version", $version );
        self::assertEquals( 1, $version->contentId, "Content ID not correctly set" );
        self::assertEquals( 2, $version->id, "ID not correctly set" );
        self::assertEquals( array( "eng-GB" => "eZ Publish" ), $version->name, "Name not correctly set" );
        self::assertEquals( 14, $version->getOwnerId(), "Owner ID not correctly set" );
        self::assertEquals( 1, $version->getSectionId(), "Section ID not correctly set" );
        self::assertEquals( 1, $version->getTypeId(), "Type ID not correctly set" );
        self::assertEquals( Content::STATUS_PUBLISHED, $version->getContentStatus(), "Content status not correctly set" );
    }

    /**
     * Test the Content Service load operation
     *
     * @group contentService
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers \ezp\Content\Service::loadVersion
     */
    public function testLoadVersionWithWrongVersionNumber()
    {
        $this->service->loadVersion( 1, 3 );
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
        $this->assertInstanceOf( "ezp\\Base\\Collection", $content->versions );
        $this->assertEquals( 2, count( $content->versions ) );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[1] );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->versions[2] );

        $this->assertEquals( 1, $content->versions[1]->id );
        $this->assertEquals( 1, $content->versions[1]->contentId );
        $this->assertEquals( 1, $content->versions[1]->versionNo );
        $this->assertEquals( 1310792400, $content->versions[1]->modified );
        $this->assertEquals( 1310792400, $content->versions[1]->created );
        $this->assertEquals( 14, $content->versions[1]->creatorId );
        $this->assertEquals( Version::STATUS_PUBLISHED, $content->versions[1]->status );

        $this->assertEquals( 2, $content->versions[2]->id );
        $this->assertEquals( 1, $content->versions[1]->contentId );
        $this->assertEquals( 2, $content->versions[2]->versionNo );
        $this->assertEquals( 1310793400, $content->versions[2]->modified );
        $this->assertEquals( 1310793400, $content->versions[2]->created );
        $this->assertEquals( 14, $content->versions[2]->creatorId );
        $this->assertEquals( Version::STATUS_DRAFT, $content->versions[2]->status );
    }

    /**
     * Test the getCurrentVersion() method after having loaded the content with the service
     *
     * @group contentService
     * @covers \ezp\Content\Service::load
     * @covers \ezp\Content::getCurrentVersion
     */
    public function testGetCurrentVersion()
    {
        $content = $this->service->load( 1 );
        $currentVersion = $content->getCurrentVersion();
        $this->assertInstanceOf( "ezp\\Content\\Version", $currentVersion );
        $this->assertInstanceOf( "ezp\\Content\\Version\\Concrete", $currentVersion );
        $this->assertEquals( 1, $currentVersion->versionNo );

        $version = $this->service->loadVersion( 1, 2 );
        $this->assertInstanceOf( "ezp\\Content\\Version", $version );
        $this->assertInstanceOf( "ezp\\Content\\Version\\Concrete", $version );

        $content = $version->getContent();
        $currentVersion = $content->getCurrentVersion();
        $this->assertInstanceOf( "ezp\\Content\\Version", $currentVersion );
        $this->assertInstanceOf( "ezp\\Content\\Version\\Proxy", $currentVersion );

        $currentVersion->getFields();// force load of proxied object so we can inspect it
        $refType = new ReflectionObject( $currentVersion );
        $refProxiedObject = $refType->getProperty( 'proxiedObject' );
        $refProxiedObject->setAccessible( true );
        $proxiedVersion = $refProxiedObject->getValue( $currentVersion );
        $this->assertInstanceOf( "ezp\\Content\\Version", $proxiedVersion );
        $this->assertInstanceOf( "ezp\\Content\\Version\\Concrete", $proxiedVersion );

        $this->assertEquals( 1, $content->currentVersionNo );
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
                $this->assertEquals( 1, $version->status );
            }
            else if ( $version->id == 2 )
            {
                $this->assertEquals( 1310793400, $version->modified );
                $this->assertEquals( 1310793400, $version->created );
                $this->assertEquals( 0, $version->status );
            }

            $this->assertInstanceOf( 'ezp\\Base\\Collection', $version->getFields() );
        }
        $this->assertEquals( array( 1 => true, 2 => true ), $foundVersions, "The versions returned is not correct" );
    }

    /**
     * Test the Content Service listVersions operation
     * with a wrong Content argument
     *
     * @group contentService
     * @covers \ezp\Content\Service::listVersions
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testListVersionsNotExisting()
    {
        $this->service->listVersions( 999 );
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
        $locations = $content->GetLocations();
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
     * @group contentService
     * @covers \ezp\Content\Service::delete
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteNotExisting()
    {
        $content = new ConcreteContent( $this->contentType, $this->anonymousUser );
        $content->getState( "properties" )->id = 999;
        $this->service->delete( $content );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotExisting()
    {
        $this->service->load( 0 );
    }

    /**
     * Test the Content Service delete operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::delete
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        $content = $this->service->load( 1 );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->delete( $content );
    }

    /**
     * Tests the content service loadFields operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::loadFields
     */
    public function testLoadFields()
    {
        $content = $this->service->load( 1 );
        $fieldsDef = array();

        // First index fields definitions by id
        foreach ( $content->contentType->getFields() as $fieldDefinition )
        {
            $fieldsDef[$fieldDefinition->id] = $fieldDefinition;
        }

        foreach ( $content->versions as $version )
        {
            $fields = $this->service->loadFields( $version );
            self::assertInternalType( \PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $fields );

            foreach ( $fields as $identifier => $field )
            {
                $fieldVo = $field->getState( 'properties' );
                self::assertInstanceOf( 'ezp\\Content\\Field', $field );
                self::assertEquals(
                    $identifier,
                    $fieldsDef[$fieldVo->fieldDefinitionId]->identifier,
                    'Fields should be indexed by field type identifier'
                );
            }
        }
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::loadFields
     */
    public function testLoadFieldsNonExistingContent()
    {
        $content = new ConcreteContent( $this->contentType, $this->anonymousUser );
        $content->getState( "properties" )->id = 999;
        foreach ( $content->versions as $version )
        {
            $this->service->loadFields( $version );
        }
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     * @group contentService
     * @covers \ezp\Content\Service::loadFields
     */
    public function testLoadFieldsNonExisitingVersion()
    {
        $this->service->loadFields( new ConcreteVersion( $this->service->load( 1 ) ) );
    }

    /**
     * Compares original content properties to its copy's
     * @param \ezp\Content $content
     * @param \ezp\Content $copy
     */
    private function compareCopyContentProperties( Content $content, Content $copy )
    {
        self::assertEquals( $content->sectionId, $copy->sectionId, "Section ID does not match" );
        self::assertEquals( $content->typeId, $copy->typeId, "Type ID does not match" );
        self::assertEquals( $content->ownerId, $copy->ownerId, "Owner ID does not match" );
        self::assertEquals( $content->currentVersionNo, $copy->currentVersionNo, "Current version no does not match" );
        self::assertEquals( 0, count( $copy->getLocations() ), "Locations must be empty" );
    }

    /**
     * Compares original content version to its copy
     * @param \ezp\Content\Version $version
     * @param \ezp\Content\Version $copyVersion
     */
    private function compareCopyContentVersions( ConcreteVersion $version, ConcreteVersion $copyVersion )
    {
        self::assertInstanceOf( 'ezp\\Content\\Version', $copyVersion );
        self::assertEquals( $version->versionNo, $copyVersion->versionNo, "Version number does not match" );
        self::assertEquals( $version->creatorId, $copyVersion->creatorId, "Creator ID does not match" );

        // Compare Fields
        foreach ( $version->getFields() as $identifier => $field )
        {
            self::assertTrue( isset( $copyVersion->fields[$identifier] ) );
            self::assertInstanceOf( 'ezp\\Content\\Field', $copyVersion->fields[$identifier] );

            $fieldVo = $field->getState( 'properties' );
            $copyFieldVo = $copyVersion->fields[$identifier]->getState( 'properties' );
            self::assertSame( $fieldVo->type, $copyFieldVo->type, "Field type must be the same for copy" );
            self::assertEquals( $fieldVo->value->data, $copyFieldVo->value->data, "Field value must be the same for copy" );
            self::assertSame( $fieldVo->language, $copyFieldVo->language, "Field language must be the same for copy" );
            self::assertSame( $fieldVo->versionNo, $copyFieldVo->versionNo, "Field version number must be the same for copy" );
        }
    }

    /**
     * Tests ContentService::copy() operation
     * @group contentService
     * @covers \ezp\Content\Service::copy
     */
    public function testCopyAllVersions()
    {
        $time = time();
        $content = $this->service->load( 1 );
        $copy = $this->service->copy( $content );

        self::assertInstanceOf( 'ezp\\Content', $copy );
        $this->compareCopyContentProperties( $content, $copy );

        // Compare original and copy versions
        self::assertEquals( count( $content->versions ), count( $copy->versions ), "Copy content must have same amount of versions" );
        foreach ( $content->versions as $versionNo => $version )
        {
            self::assertTrue( isset( $copy->versions[$versionNo] ), "Version numbers should be maintained on content copy" );
            $this->compareCopyContentVersions( $version, $copy->versions[$versionNo] );
        }

        // Copy versions
        foreach ( $copy->versions as $versionNo => $version )
        {
            self::assertEquals( $copy->id, $version->contentId );
            self::assertGreaterThanOrEqual( $time, $version->modified );
            self::assertGreaterThanOrEqual( $time, $version->created );
        }
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::copy
     */
    public function testCopyVersion1()
    {
        $time = time();
        $versionNoToCopy = 1;
        $content = $this->service->load( 1 );
        $version = $content->versions[$versionNoToCopy];
        $copy = $this->service->copy( $content, $version );

        self::assertInstanceOf( 'ezp\\Content', $copy );
        $this->compareCopyContentProperties( $content, $copy );
        self::assertEquals( 1, count( $copy->versions ), "Copying content in one version should only result one version" );
        self::assertTrue( isset( $copy->versions[$versionNoToCopy] ), "Version number should be maintained on content copy" );
        $this->compareCopyContentVersions( $version, $copy->versions[$versionNoToCopy] );

        self::assertEquals( $copy->id, $copy->versions[$versionNoToCopy]->contentId );
        self::assertGreaterThanOrEqual( $time, $copy->versions[$versionNoToCopy]->modified );
        self::assertGreaterThanOrEqual( $time, $copy->versions[$versionNoToCopy]->created );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::copy
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCopyForbidden()
    {
        $content = $this->service->load( 1 );
        $version = $content->versions[1];

        $this->repository->setUser( $this->anonymousUser );
        $this->service->copy( $content, $version );
    }

    /**
     * Tests the addRelation operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::addRelation
     */
    public function testAddRelation()
    {
        $relation = $this->service->addRelation(
            $this->service->load( 10 ),
            $this->service->load( 14 )
        );

        $this->assertEquals( Relation::COMMON, $relation->type );
        $this->assertEquals( 1, $relation->id );
        $this->assertEquals( 10, $relation->sourceContentId );
        $this->assertEquals( $this->service->load( 10 )->getCurrentVersion()->versionNo, $relation->sourceContentVersion );
        $this->assertEquals( 14, $relation->destinationContentId );
    }

    /**
     * Tests the addRelation operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::addRelation
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testAddRelationForbidden()
    {
        $from = $this->service->load( 10 );
        $to = $this->service->load( 10 );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->addRelation( $from, $to );
    }

    /**
     * @covers \ezp\Content\Service::removeRelation
     */
    public function testRemoveRelation()
    {
        $relation = $this->service->addRelation(
            $this->service->load( 10 ),
            $this->service->load( 14 )
        );
        $this->service->removeRelation( $relation );
    }

    /**
     * @covers \ezp\Content\Service::removeRelation
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveRelationDoesNotExist()
    {
        $nonExistingRelation = new Relation( Relation::COMMON, $this->service->load( 10 ) );
        $this->service->removeRelation( $nonExistingRelation );
    }

    /**
     * @covers \ezp\Content\Service::removeRelation
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testRemoveRelationForbidden()
    {
        self::markTestIncomplete( "Missing permission impl on removeRelation()" );
        $relation = $this->service->addRelation(
            $this->service->load( 10 ),
            $this->service->load( 14 )
        );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->removeRelation( $relation );
    }

    /**
     * @covers \ezp\Content\Service::loadRelations
     */
    public function testLoadRelations()
    {
        $relation = $this->service->addRelation(
            $this->service->load( 10 ),
            $this->service->load( 14 )
        );

        $relation2 = $this->service->addRelation(
            $this->service->load( 10 ),
            $this->service->load( 42 )
        );

        $relations = $this->service->loadRelations( 10 );
        self::assertEquals( 2, count( $relations ) );

        foreach ( $relations as $fetchedRelation )
        {
            self::assertInstanceOf( '\\ezp\\Content\\Relation', $fetchedRelation, "Retrieved relations has incorrect type." );

            $relObject = null;
            switch ( $fetchedRelation->id )
            {
                case $relation->id:
                    $relObject = $relation;
                    break;

                case $relation2->id:
                    $relObject = $relation2;
                    break;

                default:
                    self::fail( "An error occured, no expected relation objects were returned." );
            }

            self::assertEquals( $relObject->destinationContentId, $fetchedRelation->destinationContentId );
            self::assertEquals( $relObject->sourceContentId, $fetchedRelation->sourceContentId );
            self::assertEquals( $relObject->sourceContentVersion, $fetchedRelation->sourceContentVersion );
            self::assertEquals( $relObject->sourceFieldDefinitionId, $fetchedRelation->sourceFieldDefinitionId );
            self::assertEquals( $relObject->type, $fetchedRelation->type );
        }
    }

    /**
     * @covers \ezp\Content\Service::loadRelations
     */
    public function testLoadRelationsNonExistingId()
    {
        $relations = $this->service->loadRelations( 4000 );
        self::assertEmpty( ( $relations ) );
    }

    /**
     * Tests the createDraftFromVersion operation
     *
     * @group contentService
     * @covers \ezp\Content\Service::createDraftFromVersion
     */
    public function testCreateDraftFromVersion()
    {
        $content = $this->service->load( 1 );
        $srcVersion = $content->getCurrentVersion();

        // Get current max version number (not necessaribly $content->currentVersionNo
        // since content may have drafts
        $aVersionNo = array();
        foreach ( $content->versions as $version )
        {
            $aVersionNo[] = $version->versionNo;
        }
        $maxVersionNo = max( $aVersionNo );

        // Try first without argument (current version)
        $draft = $this->service->createDraftFromVersion( $content );
        self::assertEquals( $maxVersionNo + 1, $draft->versionNo );
        self::assertSame( Content::STATUS_DRAFT, $draft->status );
        self::assertSame( count( $srcVersion->getFields() ), count( $draft->getFields() ) );
        foreach ( $srcVersion->fields as $identifier => $field )
        {
            self::assertTrue( isset( $draft->fields[$identifier] ) );
            self::assertSame(
                $maxVersionNo + 1,
                $draft->fields[$identifier]->versionNo,
                'Created draft fields version number must have been incremented from original version'
            );
            self::assertSame( $field->type, $draft->fields[$identifier]->type );
            self::assertEquals(
                $field->value,
                $draft->fields[$identifier]->value,
                'Created draft fields must have same value as origin'
            );
            self::assertEquals( $field->fieldDefinition->id, $draft->fields[$identifier]->fieldDefinition->id );
        }

        // Now try to create a new draft from newly created draft
        $maxVersionNo = $draft->versionNo;
        $srcVersion = $draft;
        $draft = $this->service->createDraftFromVersion( $content, $srcVersion );
        self::assertEquals( $maxVersionNo + 1, $draft->versionNo );
        self::assertSame( Content::STATUS_DRAFT, $draft->status );
        self::assertSame( count( $srcVersion->fields ), count( $draft->fields ) );
        foreach ( $srcVersion->fields as $identifier => $field )
        {
            self::assertTrue( isset( $draft->fields[$identifier] ) );
            self::assertSame(
                $maxVersionNo + 1,
                $draft->fields[$identifier]->versionNo,
                'Created draft fields version number must have been incremented from original version'
            );
            self::assertSame( $field->type, $draft->fields[$identifier]->type );
            self::assertEquals(
                $field->value,
                $draft->fields[$identifier]->value,
                'Created draft fields must have same value as origin'
            );
            self::assertEquals( $field->fieldDefinition->id, $draft->fields[$identifier]->fieldDefinition->id );
        }
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::createDraftFromVersion
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCreateDraftFromInvalidVersion()
    {
        $content = $this->service->load( 1 );
        $draft = $this->service->createDraftFromVersion( $content, new ConcreteVersion( $content ) );
    }

    /**
     * @group contentService
     * @covers \ezp\Content\Service::createDraftFromVersion
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateDraftForbidden()
    {
        $content = $this->service->load( 1 );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->createDraftFromVersion( $content, $content->getCurrentVersion() );
    }

    /**
     * Tests publishing of a version that doesn't have the DRAFT status
     * @expectedException \ezp\Base\Exception\Logic
     * @covers \ezp\Content\Service::publish
     */
    public function testPublishNonDraft()
    {
        $this->service->publish( $this->service->loadVersion( 1 ) );
    }

    /**
     * Tests publishing of a newly created content
     * @covers \ezp\Content\Service::publish
     */
    public function testPublishNewContent()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 1 );

        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );
        $content->setSection( $section );

        $content = $this->repository->getContentService()->create( $content );

        $version = $content->getCurrentVersion();
        $version->fields['name'] = __METHOD__;

        self::assertEquals( Version::STATUS_DRAFT, $version->status );

        $publishedVersion = $this->service->publish( $version );

        self::assertEquals( Version::STATUS_PUBLISHED, $publishedVersion->status );
        self::assertEquals( array( 'eng-GB' => __METHOD__ ), $publishedVersion->name );
    }

    /**
     * Tests publishing of a newly created content
     * @covers \ezp\Content\Service::publish
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testPublishForbidden()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 1 );

        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );
        $content->setSection( $section );
        $content = $this->repository->getContentService()->create( $content );

        $this->repository->setUser( $this->anonymousUser );
        $this->service->publish( $content->getCurrentVersion() );
    }

    /**
     * Tests publishing of a new version of an existing content
     * @covers \ezp\Content\Service::publish
     */
    public function testPublishNewVersion()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $location = $this->repository->getLocationService()->load( 2 );
        $section = $this->repository->getSectionService()->load( 1 );

        // Create and publish content in version 1
        $content = new ConcreteContent( $type, $this->anonymousUser );
        $content->addParent( $location );
        $content->setSection( $section );

        $content = $this->repository->getContentService()->create( $content );

        $updatedVersion = $this->service->publish( $version = $content->getCurrentVersion() );

        self::assertTrue( $version === $version->getContent()->getCurrentVersion(), "Version->getContent()->getCurrentVersion() should match the Version" );
        self::assertTrue( $content === $version->getContent(), "Version->getContent() should match Content" );
        self::assertTrue( $updatedVersion === $version, "Provided Version and returned one should be the same object" );

        $version = $this->service->createDraftFromVersion( $content );

        $version->fields['name'] = __METHOD__;

        self::assertEquals( 1, $content->currentVersionNo, "Content's currentVersionNo not correctly set" );
        self::assertEquals( Version::STATUS_DRAFT, $version->status, "Draft status not correctly set" );
        self::assertEquals( Version::STATUS_PUBLISHED, $content->versions[1]->status, "Published status not correctly set" );

        $updatedVersion = $this->service->publish( $version = $content->versions[2] );

        self::assertEquals( 2, $content->currentVersionNo, "Content's currentVersionNo not correctly set" );
        self::assertEquals( 2, $version->getContent()->currentVersionNo, "Version's Content's currentVersionNo not correctly set" );

        self::assertTrue( $version === $version->getContent()->getCurrentVersion(), "Version->getContent()->getCurrentVersion() should match the Version" );
        self::assertTrue( $content === $version->getContent(), "Version->getContent() should match Content" );
        self::assertTrue( $updatedVersion === $version, "Provided Version and returned one should be the same object" );

        self::assertEquals( Version::STATUS_ARCHIVED, $content->versions[1]->status, "Archived status not correctly set" );
        self::assertEquals( Version::STATUS_PUBLISHED, $content->versions[2]->status, "Published status not correctly set" );
    }

    /**
     * Tests content name generation
     * @covers \ezp\Content\Service::generateContentName
     */
    public function testGenerateContentName()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );

        $folderName = 'This is a regular name';
        $content = new ConcreteContent( $type, $this->anonymousUser );
        $version = $content->getCurrentVersion();
        $version->fields['name'] = $folderName;

        $service = $this->repository->getContentService();
        $refService = new ReflectionObject( $service );
        $refMethod = $refService->getMethod( 'generateContentName' );
        $refMethod->setAccessible( true );

        // Content name for folder is <short_name|name> by default
        self::assertSame( $folderName, $refMethod->invoke( $service, $version ) );

        // Adding a short name, content name generation should take it
        $folderShortName = 'This one is short';
        $version->fields['short_name'] = $folderShortName;
        self::assertSame( $folderShortName, $refMethod->invoke( $service, $version ) );
    }
}
