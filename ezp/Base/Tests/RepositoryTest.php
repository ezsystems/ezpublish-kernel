<?php
/**
 * File contains: ezp\Base\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Concrete as ConcreteContent,
    ezp\User\Role\Concrete as ConcreteRole,
    ezp\User\Policy;

/**
 * Test case for Repository class
 *
 */
class RepositoryTest extends BaseServiceTest
{
    /**
     * @covers \ezp\Base\Repository::getUser
     */
    public function testGetUser()
    {
        $this->assertInstanceOf( '\\ezp\\User', $this->repository->getUser() );
    }

    /**
     * @covers \ezp\Base\Repository::setUser
     */
    public function testSetUser()
    {
        $this->assertNotEquals( 14, $this->repository->getUser()->id );
        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertEquals( 14, $this->repository->getUser()->id );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserEditSection()
    {
        $section = $this->repository->getSectionService()->load( 1 );
        $this->assertFalse( $this->repository->canUser( 'edit', $section ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'edit', $section ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserManageRole()
    {
        $role = $this->repository->getUserService()->loadRole( 2 );
        $this->assertFalse( $this->repository->canUser( 'edit', $role ) );
        $this->assertFalse( $this->repository->canUser( '*', $role ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'edit', $role ) );
        $this->assertTrue( $this->repository->canUser( '*', $role ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserManageRoleWithPolicies()
    {
        // This fails unless user is refreshed in the bottom (policies are not updated by operations bellow)
        //$role = $this->repository->getUserService()->loadRole( 2 );
        //$this->assertFalse( $this->repository->canUser( 'edit', $role ) );
        //$this->assertFalse( $this->repository->canUser( '*', $role ) );

        $service = $this->repository->getUserService();
        $this->repository->setUser( $service->load( 14 ) );

        $contentService = $this->repository->getContentService();
        $userGroup = $service->createGroup( $service->loadGroup( 4 ), 'Editors' );// Users/Editors
        $this->repository->getLocationService()->move(// save some code by moving anonymous user to new location
            $contentService->load( 10 )->locations[0],
            $contentService->load( $userGroup->id )->locations[0]
        );

        $role = new ConcreteRole();
        $role->name = 'Role manager';
        $role->addPolicy( $policy = new Policy( $role ) );
        $policy->module = 'role';
        $policy->function = '*';
        $policy->limitations = '*';
        $role = $service->createRole( $role );
        $service->assignRole( $userGroup, $role );

        $role = $this->repository->getUserService()->loadRole( 2 );
        $this->assertTrue( $this->repository->canUser( 'edit', $role ) );
        $this->assertTrue( $this->repository->canUser( '*', $role ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserManageType()
    {
        $type = $this->repository->getContentTypeService()->load( 1 );
        $this->assertFalse( $this->repository->canUser( 'edit', $type ) );
        $this->assertFalse( $this->repository->canUser( '*', $type ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'edit', $type ) );
        $this->assertTrue( $this->repository->canUser( '*', $type ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserManageTypeWithPolicies()
    {
        // This fails unless user is refreshed in the bottom (policies are not updated by operations bellow)
        //$type = $this->repository->getContentTypeService()->load( 1 );
        //$this->assertFalse( $this->repository->canUser( 'edit', $type ) );
        //$this->assertFalse( $this->repository->canUser( '*', $type ) );

        $service = $this->repository->getUserService();
        $anonymous = $this->repository->setUser( $service->load( 14 ) );

        $contentService = $this->repository->getContentService();
        $userGroup = $service->createGroup( $service->loadGroup( 4 ), 'Editors' );// Users/Editors
        $this->repository->getLocationService()->move(// save some code by moving anonymous user to new location
            $contentService->load( 10 )->locations[0],
            $contentService->load( $userGroup->id )->locations[0]
        );

        $role = new ConcreteRole();
        $role->name = 'Type (Class) manager';
        $role->addPolicy( $policy = new Policy( $role ) );
        $policy->module = 'class';
        $policy->function = '*';
        $policy->limitations = '*';
        $role = $service->createRole( $role );
        $service->assignRole( $userGroup, $role );

        $this->repository->setUser( $anonymous );

        $type = $this->repository->getContentTypeService()->load( 1 );
        $this->assertTrue( $this->repository->canUser( 'edit', $type ) );
        $this->assertTrue( $this->repository->canUser( '*', $type ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserCreateContent()
    {
        $section = $this->repository->getSectionService()->load( 1 );
        $type = $this->repository->getContentTypeService()->load( 1 );
        $parent = $this->repository->getLocationService()->load( 2 );
        $content = new ConcreteContent( $type, $this->repository->getUser() );
        $content->setSection( $section );
        $this->assertFalse( $this->repository->canUser( 'create', $content, $parent ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'create', $content, $parent ) );
        // @todo Test using User with create limitations
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserEditContent()
    {
        $content = $this->repository->getContentService()->load( 1 );
        $this->assertFalse( $this->repository->canUser( 'edit', $content ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'edit', $content ) );
        // @todo Test using User with edit limitations
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserReadContent()
    {
        $content = $this->repository->getContentService()->load( 1 );
        $this->assertTrue( $this->repository->canUser( 'read', $content ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'read', $content ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserAssignSection()
    {
        $section = $this->repository->getSectionService()->load( 2 );
        $content = $this->repository->getContentService()->load( 1 );
        $this->assertFalse( $this->repository->canUser( 'assign', $section, $content ) );

        $admin = $this->repository->getUserService()->load( 14 );
        $this->repository->setUser( $admin );
        $this->assertTrue( $this->repository->canUser( 'assign', $section, $content ) );
    }

    /**
     * @covers \ezp\Base\Repository::canUser
     */
    public function testCanUserAssignSectionWithLimitations()
    {
        // setup (create new group, move user:10 to it and apply roles to that group)
        $service = $this->repository->getUserService();
        $anonymous = $this->repository->setUser( $service->load( 14 ) );

        $contentService = $this->repository->getContentService();
        $userGroup = $service->createGroup( $service->loadGroup( 4 ), 'Editors' );// Users/Editors
        $this->repository->getLocationService()->move(// save some code by moving anonymous user to new location
            $contentService->load( 10 )->locations[0],
            $contentService->load( $userGroup->id )->locations[0]
        );

        $role = new ConcreteRole();
        $role->name = 'Limited section assigner';

        $role->addPolicy( $policy = new Policy( $role ) );
        $policy->module = 'section';
        $policy->function = 'assign';
        $policy->limitations = array(
            'Class' => array( 1 ),
            'Section' => array( 1 ),
            'NewSection' => array( 2 ),
            'Owner' => array( '1' )
        );

        $role->addPolicy( $policy = new Policy( $role ) );
        $policy->module = 'section';
        $policy->function = 'view';
        $policy->limitations = '*';
        $role = $service->createRole( $role );
        $service->assignRole( $userGroup, $role );

        $this->repository->setUser( $anonymous );

        // test
        $section = $this->repository->getSectionService()->load( 2 );
        $this->assertTrue( $this->repository->canUser( 'view', $section ) );

        $content = $contentService->load( 1 );
        $this->assertFalse( $this->repository->canUser( 'assign', $section, $content ) );

        $content->getState( 'properties' )->ownerId = 10;
        $this->assertTrue( $this->repository->canUser( 'assign', $section, $content ) );

        $standardSection = $content->section;
        $this->assertFalse( $this->repository->canUser( 'assign', $standardSection, $content ) );

        $content->getState( 'properties' )->sectionId = 2;
        $this->assertFalse( $this->repository->canUser( 'assign', $section, $content ) );

        $content->getState( 'properties' )->sectionId = 1;
        $this->assertTrue( $this->repository->canUser( 'assign', $section, $content ) );

        $content->getState( 'properties' )->typeId = 2;
        $this->assertFalse( $this->repository->canUser( 'assign', $section, $content ) );
    }

    /**
     * @covers \ezp\Base\Repository::getContentService
     */
    public function testGetContentService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Service',
            $this->repository->getContentService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getContentLanguageService
     */
    public function testGetContentLanguageService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Language\\Service',
            $this->repository->getContentLanguageService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getContentTypeService
     */
    public function testGetContentTypeService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Type\\Service',
            $this->repository->getContentTypeService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getLocationService
     */
    public function testGetLocationService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Location\\Service',
            $this->repository->getLocationService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getSectionService
     */
    public function testSectionService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Section\\Service',
            $this->repository->getSectionService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getTrashService
     */
    public function testTrashService()
    {
        $this->assertInstanceOf(
            '\\ezp\\Content\\Location\\Trash\\Service',
            $this->repository->getTrashService()
        );
    }

    /**
     * @covers \ezp\Base\Repository::getUserService
     */
    public function testUserService()
    {
        $this->assertInstanceOf(
            '\\ezp\\User\\Service',
            $this->repository->getUserService()
        );
    }
}
