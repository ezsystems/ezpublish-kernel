<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This service provides methods for permission related operations.
 */
interface PermissionService
{
    /**
     * Get current user.
     *
     * Loads the full user object if not already loaded, if you only need to know user id use {@see getCurrentUserReference()}
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser();

    /**
     * Get current user reference.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getCurrentUserReference();

    /**
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     */
    public function setCurrentUserReference(UserReference $user);

    /**
     * Returns boolean value or an array of limitations describing user's permissions
     * on the given module and function.
     *
     * Note: boolean value describes full access (true) or no access at all (false).
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\User\UserReference|null $user User for which the
     *        information is returned, current user will be used if null
     *
     * @return bool|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess($module, $function, UserReference $user = null);

    /**
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
}
