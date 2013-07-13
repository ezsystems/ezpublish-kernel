<?php
/**
 * File containing the RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use Exception;

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
        catch ( Exception $e )
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

    /**
     * Test for the getCurrentUser() method.
     *
     * @return void
     * @group content
     * @group user
     * @see \eZ\Publish\API\Repository\Repository::getCurrentUser()
     */
    public function testGetCurrentUserReturnsAnonymousUser()
    {
        $repository = $this->getRepository();
        /**
         * @HACK this is a hack to get the Repository without current user being set
         * @todo find a way to do it differently
         */
        $currentUserProperty = new \ReflectionProperty( $repository, 'currentUser' );
        $currentUserProperty->setAccessible( true );
        $currentUserProperty->setValue( $repository, null );

        /* BEGIN: Use Case */
        // No user was previously set to the $repository
        $anonymousUser = $repository->getCurrentUser();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $anonymousUser
        );
        $this->assertEquals(
            $anonymousUser->id,
            $repository->getUserService()->loadAnonymousUser()->id
        );
    }

    /**
     * Test for the setCurrentUser() method.
     *
     * @return void
     * @group content
     * @group user
     * @see \eZ\Publish\API\Repository\Repository::setCurrentUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testSetCurrentUser()
    {
        $repository = $this->getRepository();
        /**
         * @HACK this is a hack to get the Repository without current user being set
         * @todo find a way to do it differently
         */
        $currentUserProperty = new \ReflectionProperty( $repository, 'currentUser' );
        $currentUserProperty->setAccessible( true );
        $currentUserProperty->setValue( $repository, null );

        $administratorUserId = $this->generateId( 'user', 14 );

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser( $administratorUserId );

        // Set administrator user as current user
        $repository->setCurrentUser( $administratorUser );
        /* END: Use Case */

        $this->assertSame(
            $administratorUser,
            $repository->getCurrentUser()
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAnonymousUserNo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadAnonymousUser();

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $repository->hasAccess( "content", "remove", $anonymousUser );
        /* END: Use Case */

        $this->assertFalse( $hasAccess );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessWithAnonymousUserNo
     */
    public function testHasAccessForCurrentUserNo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadAnonymousUser();

        // Set anonymous user as current user
        $repository->setCurrentUser( $anonymousUser );

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $repository->hasAccess( "content", "remove" );
        /* END: Use Case */

        $this->assertFalse( $hasAccess );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser( $administratorUserId );

        // This call will return true
        $hasAccess = $repository->hasAccess( "content", "read", $administratorUser );
        /* END: Use Case */

        $this->assertTrue( $hasAccess );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessWithAdministratorUser
     */
    public function testHasAccessForCurrentUserYes()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser( $administratorUserId );

        // Set administrator user as current user
        $repository->setCurrentUser( $administratorUser );

        // This call will return true
        $hasAccess = $repository->hasAccess( "content", "read" );
        /* END: Use Case */

        $this->assertTrue( $hasAccess );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     */
    public function testHasAccessLimited()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        // This call will return an array of permission sets describing user's access
        // to reading content
        $permissionSets = $repository->hasAccess( "content", "read" );
        /* END: Use Case */

        $this->assertInternalType(
            "array",
            $permissionSets
        );
        $this->assertNotEmpty( $permissionSets );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessForCurrentUserNo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserForAnonymousUserNo()
    {
        $repository = $this->getRepository();

        $homeId = $this->generateId( 'object', 57 );

        /* BEGIN: Use Case */
        // $homeId contains the ID of the "Home" frontpage

        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadAnonymousUser();

        // Set anonymous user as current user
        $repository->setCurrentUser( $anonymousUser );

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo( $homeId );

        // This call will return false because anonymous user does not have access
        // to content removal and hence no permission to remove given content
        $canUser = $repository->canUser( "content", "remove", $contentInfo );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if ( !$canUser )
        {
            $contentService->deleteContent( $contentInfo );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessForCurrentUserYes
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testCanUserForAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );
        $homeId = $this->generateId( 'object', 57 );

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user
        // $homeId contains the ID of the "Home" frontpage

        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser( $administratorUserId );

        // Set administrator user as current user
        $repository->setCurrentUser( $administratorUser );

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo( $homeId );

        // This call will return true
        $canUser = $repository->canUser( "content", "remove", $contentInfo );

        // Performing an action having necessary permissions will succeed
        $contentService->deleteContent( $contentInfo );
        /* END: Use Case */

        $this->assertTrue( $canUser );
        $contentService->loadContent( $homeId );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithLimitationYes()
    {
        $repository = $this->getRepository();

        $imagesFolderId = $this->generateId( 'object', 49 );

        /* BEGIN: Use Case */
        // $imagesFolderId contains the ID of the "Images" folder

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Performing an action having necessary permissions will succeed
        $imagesFolder = $contentService->loadContent( $imagesFolderId );

        // This call will return true
        $canUser = $repository->canUser( "content", "read", $imagesFolder );
        /* END: Use Case */

        $this->assertTrue( $canUser );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithLimitationNo()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $userService = $repository->getUserService();

        // Load administrator user using UserService, this does not check for permissions
        $administratorUser = $userService->loadUser( $administratorUserId );

        // This call will return false as user with Editor role does not have
        // permission to read "Users" subtree
        $canUser = $repository->canUser( "content", "read", $administratorUser );

        $contentService = $repository->getContentService();

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if ( !$canUser )
        {
            $content = $contentService->loadContent( $administratorUserId );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $userGroupContentTypeId = $this->generateId( 'type', 3 );

        /* BEGIN: Use Case */
        // $userGroupContentTypeId contains the ID of the "UserGroup" ContentType

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        // Load the "UserGroup" ContentType
        $userGroupContentType = $contentTypeService->loadContentType( $userGroupContentTypeId );

        // This call will throw "InvalidArgumentException" because $userGroupContentType
        // is an instance of \eZ\Publish\API\Repository\Values\ContentType\ContentType,
        // which can not be checked for user access
        $canUser = $repository->canUser( "content", "remove", $userGroupContentType );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithTargetYes()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId( 'location', 2 );

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'forums' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateStruct->setField( 'title', 'My awesome forums' );
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct = $locationService->newLocationCreateStruct( $homeLocationId );

        // This call will return true
        $canUser = $repository->canUser(
            "content",
            "create",
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action having necessary permissions will succeed
        $contentDraft = $contentService->createContent(
            $contentCreateStruct,
            array( $locationCreateStruct )
        );
        /* END: Use Case */

        $this->assertTrue( $canUser );
        $this->assertEquals(
            'My awesome forums',
            $contentDraft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithTargetNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId( 'location', 2 );

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" frontpage location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'forum' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateStruct->setField( 'name', 'My awesome forum' );
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct = $locationService->newLocationCreateStruct( $homeLocationId );

        // This call will return false because user with Editor role has permission to
        // create "forum" type content only under "folder" type content.
        $canUser = $repository->canUser(
            "content",
            "create",
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if ( !$canUser )
        {
            $contentDraft = $contentService->createContent(
                $contentCreateStruct,
                array( $locationCreateStruct )
            );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithMultipleTargetsYes()
    {
        $repository = $this->getRepository();

        $imagesLocationId = $this->generateId( 'location', 51 );
        $filesLocationId = $this->generateId( 'location', 52 );

        /* BEGIN: Use Case */
        // $imagesLocationId contains the ID of the "Images" location
        // $filesLocationId contains the ID of the "Files" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateStruct->setField( 'name', 'My multipurpose folder' );
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct1 = $locationService->newLocationCreateStruct( $imagesLocationId );
        $locationCreateStruct2 = $locationService->newLocationCreateStruct( $filesLocationId );
        $locationCreateStructs = array( $locationCreateStruct1, $locationCreateStruct2 );

        // This call will return true
        $canUser = $repository->canUser(
            "content",
            "create",
            $contentCreateStruct,
            $locationCreateStructs
        );

        // Performing an action having necessary permissions will succeed
        $contentDraft = $contentService->createContent( $contentCreateStruct, $locationCreateStructs );
        /* END: Use Case */

        $this->assertTrue( $canUser );
        $this->assertEquals(
            'My multipurpose folder',
            $contentDraft->getFieldValue( 'name' )->text
        );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithMultipleTargetsNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId( 'location', 2 );
        $administratorUsersLocationId = $this->generateId( 'location', 13 );

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location
        // $administratorUsersLocationId contains the ID of the "Administrator users" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'forums' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateStruct->setField( 'name', 'My awesome forums' );
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct1 = $locationService->newLocationCreateStruct( $homeLocationId );
        $locationCreateStruct2 = $locationService->newLocationCreateStruct( $administratorUsersLocationId );
        $locationCreateStructs = array( $locationCreateStruct1, $locationCreateStruct2 );

        // This call will return false because user with Editor role does not have permission to
        // create content in the "Administrator users" location subtree
        $canUser = $repository->canUser(
            "content",
            "create",
            $contentCreateStruct,
            $locationCreateStructs
        );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if ( !$canUser )
        {
            $contentDraft = $contentService->createContent( $contentCreateStruct, $locationCreateStructs );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $homeId = $this->generateId( 'object', 57 );

        /* BEGIN: Use Case */
        // $homeId contains the ID of the "Home" frontpage

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo( $homeId );

        // This call will throw "InvalidArgumentException" because $targets argument must be an
        // instance of \eZ\Publish\API\Repository\Values\ValueObject class or an array of the same
        $canUser = $repository->canUser(
            "content",
            "remove",
            $contentInfo,
            new \stdClass()
        );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetURLAliasService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentExceptionVariant()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'forum' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateStruct->setField( 'name', 'My awesome forum' );
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $urlAliasService = $repository->getURLAliasService();
        $rootUrlAlias = $urlAliasService->lookUp( "/" );

        // This call will throw "InvalidArgumentException" because $rootAlias is not a valid target object
        $canUser = $repository->canUser(
            "content",
            "create",
            $contentCreateStruct,
            $rootUrlAlias
        );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCanUserThrowsBadStateException()
    {
        $this->markTestIncomplete(
            "Cannot be tested on current fixture since policy with unsupported limitation value is not available."
        );
    }
}
