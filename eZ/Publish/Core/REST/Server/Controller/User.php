<?php
/**
 * File containing the User controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Exceptions;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Repository;

use eZ\Publish\API\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException AS RestInvalidArgumentException;

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
        LocationService $locationService,
        SectionService $sectionService,
        Repository $repository )
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->contentService = $contentService;
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
            $this->urlHandler->generate( 'group', array( 'group' => '/1/5' ) ),
            'UserGroup'
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

        return new Values\RestUserGroup(
            $userGroup,
            $userGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation
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

        return new Values\RestUser(
            $user,
            $userContentInfo,
            $userMainLocation
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
        return new Values\CreatedUserGroup(
            array(
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $createdContentInfo,
                    $createdLocation
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

        $userGroupCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        //@todo Check for existence of user with same login
        //Problem being, PAPI doesn't specify any distinct error in such case

        $createdUser = $this->userService->createUser(
            $userGroupCreateStruct,
            array(
                $this->userService->loadUserGroup( $userGroupLocation->contentId )
            )
        );

        $createdContentInfo = $createdUser->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation( $createdContentInfo->mainLocationId );
        return new Values\CreatedUser(
            array(
                'user' => new Values\RestUser(
                    $createdUser,
                    $createdContentInfo,
                    $createdLocation
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

        return new Values\RestUserGroup(
            $updatedGroup,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation
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

        return new Values\RestUser(
            $updatedUser,
            $updatedContentInfo,
            $mainLocation
        );
    }

    /**
     * Given user group is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
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

        return new Values\ResourceDeleted();
    }

    /**
     * Given user is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
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

        return new Values\ResourceDeleted();
    }

    /**
     * Loads a list of users assigned to role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserList|\eZ\Publish\Core\REST\Server\Values\UserRefList
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

                $restUsers[] = new Values\RestUser( $user, $userContentInfo, $userLocation );
            }
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.userlist' )
        {
            return new Values\UserList( $restUsers, $this->request->path );
        }

        return new Values\UserRefList( $restUsers, $this->request->path );
    }

    /**
     * Loads a list of user groups assigned to role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupList|\eZ\Publish\Core\REST\Server\Values\UserGroupRefList
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

                $restUserGroups[] = new Values\RestUserGroup( $userGroup, $userGroupContentInfo, $userGroupLocation );
            }
        }

        if ( $this->getMediaType( $this->request ) === 'application/vnd.ez.api.usergrouplist' )
        {
            return new Values\UserGroupList( $restUserGroups, $this->request->path );
        }

        return new Values\UserGroupRefList( $restUserGroups, $this->request->path );
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
            $restUserGroups[] = new Values\RestUserGroup( $subGroup, $subGroupContentInfo, $subGroupLocation );
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
            $restUserGroups[] = new Values\RestUserGroup( $userGroup, $userGroupContentInfo, $userGroupLocation );
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
        $urlValues = $this->urlHandler->parse( 'groupUsers', $this->request->path );

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath( $urlValues['group'] )
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $users = $this->userService->loadUsersOfUserGroup( $userGroup );

        $restUsers = array();
        foreach ( $users as $user )
        {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();
            $userLocation = $this->locationService->loadLocation( $userContentInfo->mainLocationId );
            $restUsers[] = new Values\RestUser( $user, $userContentInfo, $userLocation );
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
            $restUserGroups[] = new Values\RestUserGroup( $userGroup, $userGroupContentInfo, $userGroupLocation );
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

        //@todo Error handling if user is already in the group
        //Reason being that UserService::assignUserToUserGroup by specs
        // does nothing if the user is already a member of the group
        $this->userService->assignUserToUserGroup( $user, $userGroup );

        $userGroups = $this->userService->loadUserGroupsOfUser( $user );
        $restUserGroups = array();
        foreach ( $userGroups as $userGroup )
        {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation( $userGroupContentInfo->mainLocationId );
            $restUserGroups[] = new Values\RestUserGroup( $userGroup, $userGroupContentInfo, $userGroupLocation );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->urlHandler->generate( 'userGroups', array( 'user' => $urlValues['user'] ) ),
            $urlValues['user']
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58
     *
     * @param string $path
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
