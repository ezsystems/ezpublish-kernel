<?php
/**
 * File containing the RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the Repository using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Repository
 * @group integration
 */
class RepositoryTest extends BaseTest
{
    /**
     * Test for the getContentService() method.
     *
     * @return void
     * @group content
     * @group user
     * @see \eZ\Publish\API\Repository\Repository::getContentService()
     */
    public function testGetContentService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ContentService',
            $repository->getContentService()
        );
    }

    /**
     * Test for the getContentLanguageService() method.
     *
     * @return void
     * @group language
     * @see \eZ\Publish\API\Repository\Repository::getContentLanguageService()
     */
    public function testGetContentLanguageService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\LanguageService',
            $repository->getContentLanguageService()
        );
    }

    /**
     * Test for the getContentTypeService() method.
     *
     * @return void
     * @group content-type
     * @group field-type
     * @group user
     * @see \eZ\Publish\API\Repository\Repository::getContentTypeService()
     *
     */
    public function testGetContentTypeService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ContentTypeService',
            $repository->getContentTypeService()
        );
    }

    /**
     * Test for the getLocationService() method.
     *
     * @return void
     * @group location
     * @see \eZ\Publish\API\Repository\Repository::getLocationService()
     *
     */
    public function testGetLocationService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\LocationService',
            $repository->getLocationService()
        );
    }

    /**
     * Test for the getSectionService() method.
     *
     * @return void
     * @group section
     * @see \eZ\Publish\API\Repository\Repository::getSectionService()
     */
    public function testGetSectionService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SectionService',
            $repository->getSectionService()
        );
    }

    /**
     * Test for the getUserService() method.
     *
     * @return void
     * @group user
     * @see \eZ\Publish\API\Repository\Repository::getUserService()
     */
    public function testGetUserService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\UserService',
            $repository->getUserService()
        );
    }

    /**
     * Test for the getTrashService() method.
     *
     * @return void
     * @group trash
     * @see \eZ\Publish\API\Repository\Repository::getTrashService()
     *
     */
    public function testGetTrashService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\TrashService',
            $repository->getTrashService()
        );
    }

    /**
     * Test for the getRoleService() method.
     *
     * @return void
     * @group role
     * @see \eZ\Publish\API\Repository\Repository::getRoleService()
     */
    public function testGetRoleService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\RoleService',
            $repository->getRoleService()
        );
    }

    /**
     * Test for the getUrlAliasService() method.
     *
     * @return void
     * @group url-alias
     * @see \eZ\Publish\API\Repository\Repository::getUrlAliasService()
     */
    public function testGetURLAliasService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\URLAliasService',
            $repository->getURLAliasService()
        );
    }

    /**
     * Test for the getUrlWildcardService() method.
     *
     * @return void
     * @group url-wildcard
     * @see \eZ\Publish\API\Repository\Repository::getUrlWildcardService()
     */
    public function testGetURLWildcardService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\URLWildcardService',
            $repository->getURLWildcardService()
        );
    }

    /**
     * Test for the getObjectStateService()
     *
     * @return void
     * @group object-state
     * @see \eZ\Publish\API\Repository\Repository::getObjectStateService()
     */
    public function testGetObjectStateService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ObjectStateService',
            $repository->getObjectStateService()
        );
    }

    /**
     * Test for the getFieldTypeService()
     *
     * @return void
     * @group object-state
     * @see \eZ\Publish\API\Repository\Repository::getFieldTypeService()
     */
    public function testGetFieldTypeService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\FieldTypeService',
            $repository->getFieldTypeService()
        );
    }

    /**
     * Test for the getSearchService() method.
     *
     * @return void
     * @group search
     * @see \eZ\Publish\API\Repository\Repository::getSearchService()
     */
    public function testGetSearchService()
    {
        $repository = $this->getRepository();

        if ( $repository instanceof \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub )
        {
            $this->markTestSkipped( 'SearchService is not available in the memory implementation.' );
        }

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SearchService',
            $repository->getSearchService()
        );
    }

    /**
     * Test for the commit() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::commit()
     *
     * @return void
     */
    public function testCommit()
    {
        $repository = $this->getRepository();

        try
        {
            $repository->beginTransaction();
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }
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
        $repository = $this->getRepository();
        $repository->commit();
    }

    /**
     * Test for the rollback() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     *
     * @return void
     */
    public function testRollback()
    {
        $repository = $this->getRepository();
        $repository->beginTransaction();
        $repository->rollback();
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
        $repository = $this->getRepository();
        $repository->rollback();
    }
}
