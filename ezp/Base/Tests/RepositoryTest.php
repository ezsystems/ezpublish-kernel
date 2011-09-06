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
    ezp\User\Role,
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
        $contentService = $this->repository->getContentService();

        $parentUserGroup = $service->loadGroup( 4 );
        $userGroup = $service->createGroup( $parentUserGroup, 'Editors' );// Users/Editors

        $userContent =  $contentService->load( 10 );
        $groupContent =  $contentService->load( $userGroup->id );
        $this->repository->getLocationService()->move(
            $userContent->locations[0],
            $groupContent->locations[0]
        );

        $role = new Role();
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
