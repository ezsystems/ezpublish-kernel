<?php

/**
 * File containing the User controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
use eZ\Publish\API\Repository\Values\User\User as RepositoryUser;
use eZ\Publish\API\Repository\Exceptions as ApiExceptions;
use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException as RestNotFoundException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * User controller.
 */
class User extends RestController
{
    /**
     * User service.
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Role service.
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Content service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Location service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Section service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Repository.
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * Construct controller.
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
        Repository $repository
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->sectionService = $sectionService;
        $this->repository = $repository;
    }

    /**
     * Redirects to the root user group.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\PermanentRedirect
     */
    public function loadRootUserGroup()
    {
        //@todo Replace hardcoded value with one loaded from settings
        return new Values\PermanentRedirect(
            $this->router->generate('ezpublish_rest_loadUserGroup', array('groupPath' => '/1/5'))
        );
    }

    /**
     * Loads a user group for the given path.
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function loadUserGroup($groupPath)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        if (trim($userGroupLocation->pathString, '/') != $groupPath) {
            throw new NotFoundException(
                "Could not find location with path string $groupPath"
            );
        }

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );
        $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

        return new Values\CachedValue(
            new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            ),
            array('locationId' => $userGroupLocation->id)
        );
    }

    /**
     * Loads a user for the given ID.
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function loadUser($userId)
    {
        $user = $this->userService->loadUser($userId);

        $userContentInfo = $user->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

        try {
            $userMainLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $relations = $this->contentService->loadRelations($user->getVersionInfo());
        } catch (UnauthorizedException $e) {
            // TODO: Hack for special case to allow current logged in user to load him/here self (but not relations)
            if ($user->id == $this->repository->getCurrentUser()->id) {
                $userMainLocation = $this->repository->sudo(
                    function () use ($userContentInfo) {
                        return $this->locationService->loadLocation($userContentInfo->mainLocationId);
                    }
                );
                // user may not have permissions to read related content, for security reasons do not use sudo().
                $relations = array();
            } else {
                throw $e;
            }
        }

        return new Values\CachedValue(
            new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userMainLocation,
                $relations
            ),
            array('locationId' => $userContentInfo->mainLocationId)
        );
    }

    /**
     * Create a new user group under the given parent
     * To create a top level group use /user/groups/1/5/subgroups.
     *
     * @param $groupPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedUserGroup
     */
    public function createUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $createdUserGroup = $this->userService->createUserGroup(
            $this->inputDispatcher->parse(
                new Message(
                    array('Content-Type' => $request->headers->get('Content-Type')),
                    $request->getContent()
                )
            ),
            $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            )
        );

        $createdContentInfo = $createdUserGroup->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUserGroup(
            array(
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUserGroup->getVersionInfo())
                ),
            )
        );
    }

    /**
     * Create a new user group in the given group.
     *
     * @param $groupPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedUser
     */
    public function createUser($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );
        $userGroup = $this->userService->loadUserGroup($userGroupLocation->contentId);

        $userCreateStruct = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );

        try {
            $createdUser = $this->userService->createUser($userCreateStruct, array($userGroup));
        } catch (ApiExceptions\InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        $createdContentInfo = $createdUser->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUser(
            array(
                'user' => new Values\RestUser(
                    $createdUser,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUser->getVersionInfo())
                ),
            )
        );
    }

    /**
     * Updates a user group.
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function updateUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ),
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $userGroup->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedGroup = $this->userService->updateUserGroup($userGroup, $updateStruct->userGroupUpdateStruct);
        $contentType = $this->contentTypeService->loadContentType(
            $updatedGroup->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\RestUserGroup(
            $updatedGroup,
            $contentType,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation,
            $this->contentService->loadRelations($updatedGroup->getVersionInfo())
        );
    }

    /**
     * Updates a user.
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser
     */
    public function updateUser($userId, Request $request)
    {
        $user = $this->userService->loadUser($userId);

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ),
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $user->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedUser = $this->userService->updateUser($user, $updateStruct->userUpdateStruct);
        $updatedContentInfo = $updatedUser->getVersionInfo()->getContentInfo();
        $mainLocation = $this->locationService->loadLocation($updatedContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($updatedContentInfo->contentTypeId);

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            $this->contentService->loadRelations($updatedUser->getVersionInfo())
        );
    }

    /**
     * Given user group is deleted.
     *
     * @param $groupPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteUserGroup($groupPath)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        // Load one user to see if user group is empty or not
        $users = $this->userService->loadUsersOfUserGroup($userGroup, 0, 1);
        if (!empty($users)) {
            throw new Exceptions\ForbiddenException('Non-empty user groups cannot be deleted');
        }

        $this->userService->deleteUserGroup($userGroup);

        return new Values\NoContent();
    }

    /**
     * Given user is deleted.
     *
     * @param $userId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteUser($userId)
    {
        $user = $this->userService->loadUser($userId);

        if ($user->id == $this->repository->getCurrentUser()->id) {
            throw new Exceptions\ForbiddenException('Currently authenticated user cannot be deleted');
        }

        $this->userService->deleteUser($user);

        return new Values\NoContent();
    }

    /**
     * Loads users.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserList|\eZ\Publish\Core\REST\Server\Values\UserRefList
     */
    public function loadUsers(Request $request)
    {
        $restUsers = array();

        try {
            if ($request->query->has('roleId')) {
                $restUsers = $this->loadUsersAssignedToRole(
                    $this->requestParser->parseHref($request->query->get('roleId'), 'roleId')
                );
            } elseif ($request->query->has('remoteId')) {
                $restUsers = array(
                    $this->buildRestUserObject(
                        $this->userService->loadUser(
                            $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'))->id
                        )
                    ),
                );
            } elseif ($request->query->has('login')) {
                $restUsers = array(
                    $this->buildRestUserObject(
                        $this->userService->loadUserByLogin($request->query->get('login'))
                    ),
                );
            } elseif ($request->query->has('email')) {
                foreach ($this->userService->loadUsersByEmail($request->query->get('email')) as $user) {
                    $restUsers[] = $this->buildRestUserObject($user);
                }
            }
        } catch (ApiExceptions\UnauthorizedException $e) {
            $restUsers = [];
        }

        if (empty($restUsers)) {
            throw new NotFoundException('No users were found with the given filter');
        }

        if ($this->getMediaType($request) === 'application/vnd.ez.api.userlist') {
            return new Values\UserList($restUsers, $request->getPathInfo());
        }

        return new Values\UserRefList($restUsers, $request->getPathInfo());
    }

    public function verifyUsers(Request $request)
    {
        // We let the NotFoundException loadUsers throws if there are no results pass.
        $this->loadUsers($request)->users;

        return new Values\OK();
    }

    /**
     * Loads a list of users assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUser[]
     */
    public function loadUsersAssignedToRole($roleId)
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUsers = array();

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserRoleAssignment) {
                $restUsers[] = $this->buildRestUserObject($roleAssignment->getUser());
            }
        }

        return $restUsers;
    }

    /**
     * @return Values\RestUser
     */
    private function buildRestUserObject(RepositoryUser $user)
    {
        return new Values\RestUser(
            $user,
            $this->contentTypeService->loadContentType($user->contentInfo->contentTypeId),
            $user->contentInfo,
            $this->locationService->loadLocation($user->contentInfo->mainLocationId),
            $this->contentService->loadRelations($user->getVersionInfo())
        );
    }

    /**
     * Loads user groups.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupList|\eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadUserGroups(Request $request)
    {
        $restUserGroups = array();
        if ($request->query->has('id')) {
            $userGroup = $this->userService->loadUserGroup($request->query->get('id'));
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupMainLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups = array(
                new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupMainLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                ),
            );
        } elseif ($request->query->has('roleId')) {
            $restUserGroups = $this->loadUserGroupsAssignedToRole($request->query->get('roleId'));
        } elseif ($request->query->has('remoteId')) {
            $restUserGroups = array(
                $this->loadUserGroupByRemoteId($request),
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ez.api.usergrouplist') {
            return new Values\UserGroupList($restUserGroups, $request->getPathInfo());
        }

        return new Values\UserGroupRefList($restUserGroups, $request->getPathInfo());
    }

    /**
     * Loads a user group by its remote ID.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup
     */
    public function loadUserGroupByRemoteId(Request $request)
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'));
        $userGroup = $this->userService->loadUserGroup($contentInfo->id);
        $userGroupLocation = $this->locationService->loadLocation($contentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        return new Values\RestUserGroup(
            $userGroup,
            $contentType,
            $contentInfo,
            $userGroupLocation,
            $this->contentService->loadRelations($userGroup->getVersionInfo())
        );
    }

    /**
     * Loads a list of user groups assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroup[]
     */
    public function loadUserGroupsAssignedToRole($roleId)
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUserGroups = array();

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserGroupRoleAssignment) {
                $userGroup = $roleAssignment->getUserGroup();
                $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
                $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
                $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

                $restUserGroups[] = new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                );
            }
        }

        return $restUserGroups;
    }

    /**
     * Loads drafts assigned to user.
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\VersionList
     */
    public function loadUserDrafts($userId, Request $request)
    {
        $contentDrafts = $this->contentService->loadContentDrafts(
            $this->userService->loadUser($userId)
        );

        return new Values\VersionList($contentDrafts, $request->getPathInfo());
    }

    /**
     * Moves the user group to another parent.
     *
     * @param $groupPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function moveUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $locationPath = $this->requestParser->parseHref(
            $request->headers->get('Destination'),
            'groupPath'
        );

        try {
            $destinationGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($locationPath)
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $destinationGroup = $this->userService->loadUserGroup($destinationGroupLocation->contentId);
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $this->userService->moveUserGroup($userGroup, $destinationGroup);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_loadUserGroup',
                array(
                    'groupPath' => trim($destinationGroupLocation->pathString, '/') . '/' . $userGroupLocation->id,
                )
            )
        );
    }

    /**
     * Returns a list of the sub groups.
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupList|\eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadSubUserGroups($groupPath, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $subGroups = $this->userService->loadSubUserGroups(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restUserGroups = array();
        foreach ($subGroups as $subGroup) {
            $subGroupContentInfo = $subGroup->getVersionInfo()->getContentInfo();
            $subGroupLocation = $this->locationService->loadLocation($subGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($subGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $subGroup,
                $contentType,
                $subGroupContentInfo,
                $subGroupLocation,
                $this->contentService->loadRelations($subGroup->getVersionInfo())
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ez.api.usergrouplist') {
            return new Values\CachedValue(
                new Values\UserGroupList($restUserGroups, $request->getPathInfo()),
                array('locationId' => $userGroupLocation->id)
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo()),
            array('locationId' => $userGroupLocation->id)
        );
    }

    /**
     * Returns a list of user groups the user belongs to.
     * The returned list includes the resources for unassigning
     * a user group if the user is in multiple groups.
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function loadUserGroupsOfUser($userId, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $user = $this->userService->loadUser($userId);
        $userGroups = $this->userService->loadUserGroupsOfUser(
            $user,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restUserGroups = array();
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo(), $userId),
            array('locationId' => $user->contentInfo->mainLocationId)
        );
    }

    /**
     * Loads the users of the group with the given path.
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserList|\eZ\Publish\Core\REST\Server\Values\UserRefList
     */
    public function loadUsersFromGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $users = $this->userService->loadUsersOfUserGroup(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        );

        $restUsers = array();
        foreach ($users as $user) {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();
            $userLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

            $restUsers[] = new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userLocation,
                $this->contentService->loadRelations($user->getVersionInfo())
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ez.api.userlist') {
            return new Values\CachedValue(
                new Values\UserList($restUsers, $request->getPathInfo()),
                array('locationId' => $userGroupLocation->id)
            );
        }

        return new Values\CachedValue(
            new Values\UserRefList($restUsers, $request->getPathInfo()),
            array('locationId' => $userGroupLocation->id)
        );
    }

    /**
     * Unassigns the user from a user group.
     *
     * @param $userId
     * @param $groupPath
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function unassignUserFromUserGroup($userId, $groupPath)
    {
        $user = $this->userService->loadUser($userId);
        $userGroupLocation = $this->locationService->loadLocation(trim($groupPath, '/'));

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        try {
            $this->userService->unAssignUserFromUserGroup($user, $userGroup);
        } catch (ApiExceptions\InvalidArgumentException $e) {
            // User is not in the group
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $userGroups = $this->userService->loadUserGroupsOfUser($user);
        $restUserGroups = array();
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->router->generate(
                'ezpublish_rest_loadUserGroupsOfUser',
                array('userId' => $userId)
            ),
            $userId
        );
    }

    /**
     * Assigns the user to a user group.
     *
     * @param $userId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserGroupRefList
     */
    public function assignUserToUserGroup($userId, Request $request)
    {
        $user = $this->userService->loadUser($userId);

        try {
            $userGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($request->query->get('group'))
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $userGroup = $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $this->userService->assignUserToUserGroup($user, $userGroup);
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $userGroups = $this->userService->loadUserGroupsOfUser($user);
        $restUserGroups = array();
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->router->generate(
                'ezpublish_rest_loadUserGroupsOfUser',
                array('userId' => $userId)
            ),
            $userId
        );
    }

    /**
     * Creates a new session based on the credentials provided as POST parameters.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException If the login or password are incorrect or invalid CSRF
     *
     * @return Values\UserSession|Values\Conflict
     */
    public function createSession(Request $request)
    {
        /** @var $sessionInput \eZ\Publish\Core\REST\Server\Values\SessionInput */
        $sessionInput = $this->inputDispatcher->parse(
            new Message(
                array('Content-Type' => $request->headers->get('Content-Type')),
                $request->getContent()
            )
        );
        $request->attributes->set('username', $sessionInput->login);
        $request->attributes->set('password', $sessionInput->password);

        try {
            $csrfToken = '';
            $csrfTokenManager = $this->container->get('security.csrf.token_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE);
            $session = $request->getSession();
            if ($session->isStarted()) {
                if ($csrfTokenManager) {
                    $csrfToken = $request->headers->get('X-CSRF-Token');
                    if (
                        !$csrfTokenManager->isTokenValid(
                            new CsrfToken(
                                $this->container->getParameter('ezpublish_rest.csrf_token_intention'),
                                $csrfToken
                            )
                        )
                    ) {
                        throw new UnauthorizedException('Missing or invalid CSRF token', $csrfToken);
                    }
                }
            }

            $authenticator = $this->container->get('ezpublish_rest.session_authenticator');
            $token = $authenticator->authenticate($request);
            // If CSRF token has not been generated yet (i.e. session not started), we generate it now.
            // This will seamlessly start the session.
            if (!$csrfToken) {
                $csrfToken = $csrfTokenManager->getToken(
                    $this->container->getParameter('ezpublish_rest.csrf_token_intention')
                )->getValue();
            }

            return new Values\UserSession(
                $token->getUser()->getAPIUser(),
                $session->getName(),
                $session->getId(),
                $csrfToken,
                !$token->hasAttribute('isFromSession')
            );
        } catch (Exceptions\UserConflictException $e) {
            // Already logged in with another user, this will be converted to HTTP status 409
            return new Values\Conflict();
        } catch (AuthenticationException $e) {
            throw new UnauthorizedException('Invalid login or password', $request->getPathInfo());
        } catch (AccessDeniedException $e) {
            throw new UnauthorizedException($e->getMessage(), $request->getPathInfo());
        }
    }

    /**
     * Refresh given session.
     *
     * @param string $sessionId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserSession
     */
    public function refreshSession($sessionId, Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();
        $inputCsrf = $request->headers->get('X-CSRF-Token');
        if ($session === null || !$session->isStarted() || $session->getId() != $sessionId) {
            throw new RestNotFoundException('Session not valid');
        }

        return new Values\UserSession(
            $this->repository->getCurrentUser(),
            $session->getName(),
            $session->getId(),
            $inputCsrf,
            false
        );
    }

    /**
     * Deletes given session.
     *
     * @param string $sessionId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     *
     * @throws RestNotFoundException
     */
    public function deleteSession($sessionId, Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->container->get('session');
        if (!$session->isStarted() || $session->getId() != $sessionId) {
            throw new RestNotFoundException("Session not found: '{$sessionId}'.");
        }

        return new Values\DeletedUserSession(
            $this->container->get('ezpublish_rest.session_authenticator')->logout($request)
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath($path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }
}
