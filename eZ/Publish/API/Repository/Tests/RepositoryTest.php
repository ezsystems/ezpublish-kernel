<?php

/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use Exception;
use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Values\User\UserReference;

/**
 * Test case for operations in the Repository using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Repository
 * @group integration
 */
class RepositoryTest extends BaseTest
{
    /**
     * Test for the getRepository() method.
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(Repository::class, $this->getSetupFactory()->getRepository(true));
    }

    /**
     * Test for the getContentService() method.
     *
     * @group content
     * @group user
     *
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
     * @group language
     *
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
     * @group content-type
     * @group field-type
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getContentTypeService()
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
     * @group location
     *
     * @see \eZ\Publish\API\Repository\Repository::getLocationService()
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
     * @group section
     *
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
     * @group user
     *
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
     * Test for the getNotificationService() method.
     *
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getNotificationService()
     */
    public function testGetNotificationService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            NotificationService::class,
            $repository->getNotificationService()
        );
    }

    /**
     * Test for the getTrashService() method.
     *
     * @group trash
     *
     * @see \eZ\Publish\API\Repository\Repository::getTrashService()
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
     * @group role
     *
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
     * Test for the getURLAliasService() method.
     *
     * @group url-alias
     *
     * @see \eZ\Publish\API\Repository\Repository::getURLAliasService()
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
     * @group url-wildcard
     *
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
     * Test for the getObjectStateService().
     *
     * @group object-state
     *
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
     * Test for the getFieldTypeService().
     *
     * @group object-state
     *
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
     * @group search
     *
     * @see \eZ\Publish\API\Repository\Repository::getSearchService()
     */
    public function testGetSearchService()
    {
        $repository = $this->getRepository();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SearchService',
            $repository->getSearchService()
        );
    }

    /**
     * Test for the getSearchService() method.
     *
     * @group permission
     *
     * @see \eZ\Publish\API\Repository\Repository::getPermissionResolver()
     */
    public function testGetPermissionResolver()
    {
        $repository = $this->getRepository();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\PermissionResolver',
            $repository->getPermissionResolver()
        );
    }

    /**
     * Test for the commit() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::commit()
     */
    public function testCommit()
    {
        $repository = $this->getRepository();

        try {
            $repository->beginTransaction();
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }
    }

    /**
     * Test for the commit() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::commit()
     */
    public function testCommitThrowsRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        $repository = $this->getRepository();
        $repository->commit();
    }

    /**
     * Test for the rollback() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::rollback()
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
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     */
    public function testRollbackThrowsRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        $repository = $this->getRepository();
        $repository->rollback();
    }
}
