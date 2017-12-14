<?php

/**
 * File containing the eZ\Publish\API\Repository\Repository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;

/**
 * Repository interface.
 */
interface Repository
{
    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user.
     *
     * Loads the full user object if not already loaded, if you only need to know user id use {@see getCurrentUserReference()}
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser();

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user reference.
     *
     * @since 5.4.5
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getCurrentUserReference();

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::setCurrentUserReference() instead.
     *
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     */
    public function setCurrentUser(UserReference $user);

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::hasAccess() instead.
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return bool|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess($module, $function, UserReference $user = null);

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::canUser() instead.
     *
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
     *
     * Example: canUser( 'content', 'edit', $content, $location );
     *          This will check edit permission on content given the specific location, if skipped if will check on all
     *          locations.
     *
     * Example2: canUser( 'section', 'assign', $content, $section );
     *           Check if user has access to assign $content to $section.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param mixed $targets The location, parent or "assignment" value object, or an array of the same
     *
     * @return bool
     */
    public function canUser($module, $function, ValueObject $object, $targets = null);

    /**
     * Get Content Service.
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService();

    /**
     * Get Content Language Service.
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService();

    /**
     * Get Content Type Service.
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService();

    /**
     * Get Content Location Service.
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService();

    /**
     * Get Content Trash service.
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService();

    /**
     * Get Content Section Service.
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService();

    /**
     * Get Search Service.
     *
     * Get search service that lets you find content objects
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService();

    /**
     * Get User Service.
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService();

    /**
     * Get URLAliasService.
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService();

    /**
     * Get URLWildcardService.
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService();

    /**
     * Get ObjectStateService.
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService();

    /**
     * Get RoleService.
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService();

    /**
     * Get FieldTypeService.
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService();

    /**
     * Get PermissionResolver.
     *
     * @return \eZ\Publish\API\Repository\PermissionResolver
     */
    public function getPermissionResolver();

    /**
     * Begin transaction.
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction();

    /**
     * Commit transaction.
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit();

    /**
     * Rollback transaction.
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback();
}
