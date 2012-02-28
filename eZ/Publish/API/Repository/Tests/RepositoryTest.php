<?php
/**
 * File containing the RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the Repository using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Repository
 * @group integration
 */
class RepositoryTest extends BaseTest
{
    /**
     * Test for the getCurrentUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getCurrentUser()
     */
    public function testGetCurrentUser()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $currentUser = $repository->getCurrentUser();
        /* END: Use Case */

        $this->assertNotNull( $currentUser );
    }

    /**
     * Test for the setCurrentUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::setCurrentUser()
     */
    public function testSetCurrentUser()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::testSetCurrentUser() is not implemented." );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * 
     */
    public function testHasAccess()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::hasAccess() is not implemented." );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::hasAccess($module, $function, $user)
     * 
     */
    public function testHasAccessWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::hasAccess() is not implemented." );
    }

    /**
     * Test for the canUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * 
     */
    public function testCanUser()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::canUser() is not implemented." );
    }

    /**
     * Test for the getContentService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getContentService()
     */
    public function testGetContentService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\ContentService', $repository->getContentService() );
    }

    /**
     * Test for the getContentLanguageService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getContentLanguageService()
     */
    public function testGetContentLanguageService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\LanguageService', $repository->getContentLanguageService() );
    }

    /**
     * Test for the getContentTypeService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getContentTypeService()
     * 
     */
    public function testGetContentTypeService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\ContentTypeService', $repository->getContentTypeService() );
    }

    /**
     * Test for the getLocationService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getLocationService()
     * 
     */
    public function testGetLocationService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\LocationService', $repository->getLocationService() );
    }

    /**
     * Test for the getTrashService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getTrashService()
     * 
     */
    public function testGetTrashService()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::getTrashService() is not implemented." );
    }

    /**
     * Test for the getSectionService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getSectionService()
     */
    public function testGetSectionService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\SectionService', $repository->getSectionService() );
    }

    /**
     * Test for the getUserService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getUserService()
     */
    public function testGetUserService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\UserService', $repository->getUserService() );
    }

    /**
     * Test for the getRoleService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getRoleService()
     */
    public function testGetRoleService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\RoleService', $repository->getRoleService() );
    }

    /**
     * Test for the beginTransaction() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::beginTransaction()
     * 
     */
    public function testBeginTransaction()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::beginTransaction() is not implemented." );
    }

    /**
     * Test for the commit() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::commit()
     * 
     */
    public function testCommit()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::commit() is not implemented." );
    }

    /**
     * Test for the commit() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::commit()
     * @expectedException \RuntimeException
     */
    public function testCommitThrowsRuntimeException()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::commit() is not implemented." );
    }

    /**
     * Test for the rollback() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     * 
     */
    public function testRollback()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::rollback() is not implemented." );
    }

    /**
     * Test for the rollback() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     * @expectedException \RuntimeException
     */
    public function testRollbackThrowsRuntimeException()
    {
        $this->markTestIncomplete( "@TODO: Test for Repository::rollback() is not implemented." );
    }

}
