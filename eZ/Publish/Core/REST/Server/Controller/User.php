<?php
/**
 * File containing the User controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Exceptions;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Repository;

use eZ\Publish\API\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException AS RestInvalidArgumentException;
use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException AS RestNotFoundException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

/**
 * User controller
 */
class User extends RestController
{
    /**
     * User service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Repository
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\SectionService $sectionService
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SectionService $sectionService,
        Repository $repository )
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->sectionService = $sectionService;
        $this->repository = $repository;
    }

    /**
     * Redirects to the root user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\PermanentRedirect
     */
    public function loadRootUserGroup()
    {
        //@todo Replace hardcoded value with one loaded from settings
        return new Values\PermanentRedirect(
            $this->urlHandler->generate( 'group', array( 'group' => '/1/5' ) )
        );
    }

    /**
     * Loads a user group for the given path
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function loadUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'group', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );
        $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

        return new Values\RestUserGroup(
            $userGroup,
            $contentType,
            $userGroupContentInfo,
            $userGroupLocation,
            $this->contentService->loadRelations( $userGroup->getVersionInfo() )
        );
    }

    /**
     * Loads a user for the given ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function loadUser()
    {
        $urlValues = $this->urlHandler->parse( 'user', $this->request->path );

        $user = $this->userService->loadUser(
            $urlValues['user']
        );

        $userContentInfo = $user->getVersionInfo()->getContentInfo();
        $userMainLocation = $this->locationService->loadLocation( $userContentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $userContentInfo->contentTypeId );

        return new Values\RestUser(
            $user,
            $contentType,
            $userContentInfo,
            $userMainLocation,
            $this->contentService->loadRelations( $user->getVersionInfo() )
        );
    }

    /**
     * Create a new user group under the given parent
     * To create a top level group use /user/groups/subgroups
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedUserGroup
     */
    public function createUserGroup()
    {
        try
        {
            $urlValues = $this->urlHandler->parse( 'groupSubgroups', $this->request->path );
            $userGroupPath = $urlValues['group'];
        }
        catch ( RestInvalidArgumentException $e )
        {
            try
            {
                $this->urlHandler->parse( 'rootUserGroupSubGroups', $this->request->path );
                //@todo Load from settings instead of using hardcoded value
                $userGroupPath = '/1/5';
            }
            catch ( RestInvalidArgumentException $e )
            {
                throw new Exceptions\BadRequestException( 'Unrecognized user group resource' );
            }
        }

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $userGroupPath )
        );

        $createdUserGroup = $this->userService->createUserGroup(
            $this->inputDispatcher->parse(
                new Message(
                    array( 'Content-Type' => $this->request->contentType ),
                    $this->request->body
                )
            ),
            $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            )
        );

        $createdContentInfo = $createdUserGroup->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation( $createdContentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $createdContentInfo->contentTypeId );

        return new Values\CreatedUserGroup(
            array(
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations( $createdUserGroup->getVersionInfo() )
                )
            )
        );
    }

    /**
     * Create a new user group in the given group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedUser
     */
    public function createUser()
    {
        $urlValues = $this->urlHandler->parse( 'groupUsers', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );
        $userGroup = $this->userService->loadUserGroup( $userGroupLocation->contentId );

        $userCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            $createdUser = $this->userService->createUser( $userCreateStruct, array( $userGroup ) );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        $createdContentInfo = $createdUser->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation( $createdContentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $createdContentInfo->contentTypeId );

        return new Values\CreatedUser(
            array(
                'user' => new Values\RestUser(
                    $createdUser,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations( $createdUser->getVersionInfo() )
                )
            )
        );
    }

    /**
     * Updates a user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function updateUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'group', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $this->request->path
                ),
                $this->request->body
            )
        );

        if ( $updateStruct->sectionId !== null )
        {
            $section = $this->sectionService->loadSection( $updateStruct->sectionId );
            $this->sectionService->assignSection(
                $userGroup->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedGroup = $this->userService->updateUserGroup( $userGroup, $updateStruct->userGroupUpdateStruct );
        $contentType = $this->contentTypeService->loadContentType(
            $updatedGroup->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\RestUserGroup(
            $updatedGroup,
            $contentType,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation,
            $this->contentService->loadRelations( $updatedGroup->getVersionInfo() )
        );
    }

    /**
     * Updates a user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function updateUser()
    {
        $urlValues = $this->urlHandler->parse( 'user', $this->request->path );

        $user = $this->userService->loadUser( $urlValues['user'] );

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $this->request->path
                ),
                $this->request->body
            )
        );

        if ( $updateStruct->sectionId !== null )
        {
            $section = $this->sectionService->loadSection( $updateStruct->sectionId );
            $this->sectionService->assignSection(
                $user->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedUser = $this->userService->updateUser( $user, $updateStruct->userUpdateStruct );
        $updatedContentInfo = $updatedUser->getVersionInfo()->getContentInfo();
        $mainLocation = $this->locationService->loadLocation( $updatedContentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $updatedContentInfo->contentTypeId );

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            $this->contentService->loadRelations( $updatedUser->getVersionInfo() )
        );
    }

    /**
     * Given user group is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'group', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        // Load one user to see if user group is empty or not
        $users = $this->userService->loadUsersOfUserGroup( $userGroup, 0, 1 );
        if ( !empty( $users ) )
        {
            throw new Exceptions\ForbiddenException( "Non-empty user groups cannot be deleted" );
        }

        $this->userService->deleteUserGroup( $userGroup );

        return new Values\NoContent();
    }

    /**
     * Given user is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteUser()
    {
        $urlValues = $this->urlHandler->parse( 'user', $this->request->path );

        $user = $this->userService->loadUser(
            $urlValues['user']
        );

        if ( $user->id == $this->repository->getCurrentUser()->id )
        {
            throw new Exceptions\ForbiddenException( "Currently authenticated user cannot be deleted" );
        }

        $this->userService->deleteUser( $user );

        return new Values\NoContent();
    }

    /**
     * Loads users
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserList|\eZ\Publish\Core\REST\Server\Values\UserRefList
     */
    public function loadUsers()
    {
        $restUsers = array();
        if ( isset( $this->request->variables['roleId'] ) )
        {
             $restUsers = $this->loadUsersAssignedToRole();
        }
        else if ( isset( $this->request->variables['remoteId'] ) )
        {
            $restUsers = array(
                $this->loadUserByRemoteId()
            );
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.userlist' )
        {
            return new Values\UserList( $restUsers, $this->request->path );
        }

        return new Values\UserRefList( $restUsers, $this->request->path );
    }

    /**
     * Loads a user by its remote ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function loadUserByRemoteId()
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId( $this->request->variables['remoteId'] );
        $user = $this->userService->loadUser( $contentInfo->id );
        $userLocation = $this->locationService->loadLocation( $contentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );

        return new Values\RestUser(
            $user,
            $contentType,
            $contentInfo,
            $userLocation,
            $this->contentService->loadRelations( $user->getVersionInfo() )
        );
    }

    /**
     * Loads a list of users assigned to role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser[]
     */
    public function loadUsersAssignedToRole()
    {
        $roleValues = $this->urlHandler->parse( 'role', $this->request->variables['roleId'] );

        $role = $this->roleService->loadRole( $roleValues['role'] );
        $roleAssignments = $this->roleService->getRoleAssignments( $role );

        $restUsers = array();

        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment instanceof UserRoleAssignment )
            {
                $user = $roleAssignment->getUser();
                $userContentInfo = $user->getVersionInfo()->getContentInfo();
                $userLocation = $this->locationService->loadLocation( $userContentInfo->mainLocationId );
                $contentType = $this->contentTypeService->loadContentType( $userContentInfo->contentTypeId );

                $restUsers[] = new Values\RestUser(
                    $user,
                    $contentType,
                    $userContentInfo,
                    $userLocation,
                    $this->contentService->loadRelations( $user->getVersionInfo() )
                );
            }
        }

        return $restUsers;
    }

    /**
     * Loads user groups
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupList|\eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadUserGroups()
    {
        $restUserGroups = array();
        if ( isset( $this->request->variables['id'] ) )
        {
            $userGroup = $this->userService->loadUserGroup( $this->request->variables['id'] );
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupMainLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

            $restUserGroups = array(
                new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupMainLocation,
                    $this->contentService->loadRelations( $userGroup->getVersionInfo() )
                )
            );
        }
        else if ( isset( $this->request->variables['roleId'] ) )
        {
             $restUserGroups = $this->loadUserGroupsAssignedToRole();
        }
        else if ( isset( $this->request->variables['remoteId'] ) )
        {
            $restUserGroups = array(
                $this->loadUserGroupByRemoteId()
            );
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.usergrouplist' )
        {
            return new Values\UserGroupList( $restUserGroups, $this->request->path );
        }

        return new Values\UserGroupRefList( $restUserGroups, $this->request->path );
    }

    /**
     * Loads a user group by its remote ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function loadUserGroupByRemoteId()
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId( $this->request->variables['remoteId'] );
        $userGroup = $this->userService->loadUserGroup( $contentInfo->id );
        $userGroupLocation = $this->locationService->loadLocation( $contentInfo->mainLocationId );
        $contentType = $this->contentTypeService->loadContentType( $contentInfo->contentTypeId );

        return new Values\RestUserGroup(
            $userGroup,
            $contentType,
            $contentInfo,
            $userGroupLocation,
            $this->contentService->loadRelations( $userGroup->getVersionInfo() )
        );
    }

    /**
     * Loads a list of user groups assigned to role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup[]
     */
    public function loadUserGroupsAssignedToRole()
    {
        $roleValues = $this->urlHandler->parse( 'role', $this->request->variables['roleId'] );

        $role = $this->roleService->loadRole( $roleValues['role'] );
        $roleAssignments = $this->roleService->getRoleAssignments( $role );

        $restUserGroups = array();

        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment instanceof UserGroupRoleAssignment )
            {
                $userGroup = $roleAssignment->getUserGroup();
                $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
                $userGroupLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
                $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

                $restUserGroups[] = new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupLocation,
                    $this->contentService->loadRelations( $userGroup->getVersionInfo() )
                );
            }
        }

        return $restUserGroups;
    }

    /**
     * Loads drafts assigned to user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\VersionList
     */
    public function loadUserDrafts()
    {
        $urlValues = $this->urlHandler->parse( 'userDrafts', $this->request->path );

        $contentDrafts = $this->contentService->loadContentDrafts(
            $this->userService->loadUser( $urlValues['user'] )
        );

        return new Values\VersionList( $contentDrafts, $this->request->path );
    }

    /**
     * Moves the user group to another parent
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function moveUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'group', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $destinationParts = $this->urlHandler->parse( 'group', $this->request->destination );

        try
        {
            $destinationGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath( $destinationParts['group'] )
            );
        }
        catch ( NotFoundException $e )
        {
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        try
        {
            $destinationGroup = $this->userService->loadUserGroup( $destinationGroupLocation->contentId );
        }
        catch ( NotFoundException $e )
        {
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        $this->userService->moveUserGroup( $userGroup, $destinationGroup );

        return new Values\ResourceCreated(
            $this->urlHandler->generate(
                'group',
                array(
                    'group' => $destinationGroupLocation->pathString . $userGroupLocation->id
                )
            )
        );
    }

    /**
     * Returns a list of the sub groups
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupList|\eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadSubUserGroups()
    {
        $urlValues = $this->urlHandler->parse( 'groupSubgroups', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $subGroups = $this->userService->loadSubUserGroups( $userGroup );

        $restUserGroups = array();
        foreach ( $subGroups as $subGroup )
        {
            $subGroupContentInfo = $subGroup->getVersionInfo()->getContentInfo();
            $subGroupLocation = $this->locationService->loadLocation( $subGroupContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $subGroupContentInfo->contentTypeId );

            $restUserGroups[] = new Values\RestUserGroup(
                $subGroup,
                $contentType,
                $subGroupContentInfo,
                $subGroupLocation,
                $this->contentService->loadRelations( $subGroup->getVersionInfo() )
            );
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.usergrouplist' )
        {
            return new Values\UserGroupList( $restUserGroups, $this->request->path );
        }

        return new Values\UserGroupRefList( $restUserGroups, $this->request->path );
    }

    /**
     * Returns a list of user groups the user belongs to.
     * The returned list includes the resources for unassigning
     * a user group if the user is in multiple groups.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadUserGroupsOfUser()
    {
        $urlValues = $this->urlHandler->parse( 'userGroups', $this->request->path );

        $user = $this->userService->loadUser( $urlValues['user'] );
        $userGroups = $this->userService->loadUserGroupsOfUser( $user );

        $restUserGroups = array();
        foreach ( $userGroups as $userGroup )
        {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations( $userGroup->getVersionInfo() )
            );
        }

        return new Values\UserGroupRefList( $restUserGroups, $this->request->path, $urlValues['user'] );
    }

    /**
     * Loads the users of the group with the given path
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserList|\eZ\Publish\Core\REST\Server\Values\UserRefList
     */
    public function loadUsersFromGroup()
    {
        $questionMark = strpos( $this->request->path, '?' );
        $requestPath = $questionMark !== false ? substr( $this->request->path, 0, $questionMark ) : $this->request->path;

        $urlValues = $this->urlHandler->parse( 'groupUsers', $requestPath );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $offset = isset( $this->request->variables['offset'] ) ? (int)$this->request->variables['offset'] : 0;
        $limit = isset( $this->request->variables['limit'] ) ? (int)$this->request->variables['limit'] : -1;

        $users = $this->userService->loadUsersOfUserGroup(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : -1
        );

        $restUsers = array();
        foreach ( $users as $user )
        {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();
            $userLocation = $this->locationService->loadLocation( $userContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $userContentInfo->contentTypeId );

            $restUsers[] = new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userLocation,
                $this->contentService->loadRelations( $user->getVersionInfo() )
            );
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.userlist' )
        {
            return new Values\UserList( $restUsers, $this->request->path );
        }

        return new Values\UserRefList( $restUsers, $this->request->path );
    }

    /**
     * Unassigns the user from a user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function unassignUserFromUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'userGroup', $this->request->path );

        $user = $this->userService->loadUser( $urlValues['user'] );
        $userGroupLocation = $this->locationService->loadLocation( trim( $urlValues['group'], '/' ) );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        try
        {
            $this->userService->unAssignUserFromUserGroup( $user, $userGroup );
        }
        catch ( InvalidArgumentException $e )
        {
            // User is not in the group
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        $userGroups = $this->userService->loadUserGroupsOfUser( $user );
        $restUserGroups = array();
        foreach ( $userGroups as $userGroup )
        {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations( $userGroup->getVersionInfo() )
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->urlHandler->generate( 'userGroups', array( 'user' => $urlValues['user'] ) ),
            $urlValues['user']
        );
    }

    /**
     * Assigns the user to a user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function assignUserToUserGroup()
    {
        $urlValues = $this->urlHandler->parse( 'userGroupAssign', $this->request->path );

        $user = $this->userService->loadUser( $urlValues['user'] );

        try
        {
            $userGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath( $this->request->variables['group'] )
            );
        }
        catch ( NotFoundException $e )
        {
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        try
        {
            $userGroup = $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            );
        }
        catch ( NotFoundException $e )
        {
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        try
        {
            $this->userService->assignUserToUserGroup( $user, $userGroup );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new Exceptions\ForbiddenException( $e->getMessage() );
        }

        $userGroups = $this->userService->loadUserGroupsOfUser( $user );
        $restUserGroups = array();
        foreach ( $userGroups as $userGroup )
        {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
            $contentType = $this->contentTypeService->loadContentType( $userGroupContentInfo->contentTypeId );

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations( $userGroup->getVersionInfo() )
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->urlHandler->generate( 'userGroups', array( 'user' => $urlValues['user'] ) ),
            $urlValues['user']
        );
    }

    /**
     * Creates a new session based on the credentials provided as POST parameters
     */
    public function createSession()
    {
        /** @var $sessionInput \eZ\Publish\Core\REST\Server\Values\SessionInput */
        $sessionInput = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            $user = $this->userService->loadUserByCredentials(
                $sessionInput->login,
                $sessionInput->password
            );
        }
        catch ( NotFoundException $e )
        {
            throw new UnauthorizedException( "Invalid login or password", 0, null, $e );
        }

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->container->get( 'session' );
        /** @var $authenticationToken \Symfony\Component\Security\Core\Authentication\Token\TokenInterface */
        $authenticationToken = $this->container->get( 'security.context' )->getToken();

        if ( $session->isStarted() && $authenticationToken !== null )
        {
            /** @var $currentUser \eZ\Publish\API\Repository\Values\User\User */
            $currentUser = $authenticationToken->getUser()->getAPIUser();
            if ( $user->id == $currentUser->id )
            {
                return new Values\SeeOther(
                    $this->urlHandler->generate(
                        'userSession',
                        array( 'sessionId' => $session->getId() )
                    )
                );
            }

            $anonymousUser = $this->userService->loadAnonymousUser();
            if ( $currentUser->id != $anonymousUser->id )
            {
                // Already logged in with another user, this will be converted to HTTP status 409
                return new Values\Conflict();
            }
        }

        if ( $this->container->getParameter( 'form.type_extension.csrf.enabled' ) )
        {
            /** @var $csrfProvider \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface */
            $csrfProvider = $this->container->get( 'form.csrf_provider' );
        }

        $session->start();
        $session->set( "eZUserLoggedInID", $user->id );
        return new Values\UserSession(
            $user,
            $session->getName(),
            $session->getId(),
            isset( $csrfProvider ) ?
                $csrfProvider->generateCsrfToken( 'rest' ) :
                ""
        );
    }

    /**
     * Deletes given session.
     */
    public function deleteSession()
    {
        $urlValues = $this->urlHandler->parse( "userSession", $this->request->path );
        $sessionId = $urlValues["sessionId"];

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->container->get( 'session' );
        if ( !$session->isStarted() || $session->getId() != $sessionId )
        {
            throw new RestNotFoundException( "Session not found: '{$sessionId}'." );
        }

        $this->container->get( 'security.context' )->setToken( null );
        $session->invalidate();

        return new Values\NoContent();
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath( $path )
    {
        $pathParts = explode( '/', $path );
        return array_pop( $pathParts );
    }

    /**
     * Extracts the requested media type from $request
     *
     * @return string
     */
    protected function getMediaType()
    {
        foreach ( $this->request->mimetype as $mimeType )
        {
            if ( preg_match( '(^([a-z0-9-/.]+)\+.*$)', $mimeType['value'], $matches ) )
            {
                return $matches[1];
            }
        }
        return 'unknown/unknown';
    }
}
