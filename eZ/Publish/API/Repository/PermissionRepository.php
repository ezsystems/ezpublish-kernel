<?php
/**
 * File containing the eZ\Publish\API\Repository\PermissionRepository class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Repository interface for Repository that checks permissions
 * @package eZ\Publish\API\Repository
 */
interface PermissionRepository extends Repository
{
    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser();

    /**
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return void
     */
    public function setCurrentUser( User $user );

    /**
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null );

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
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
     * @return boolean
     */
    public function canUser( $module, $function, ValueObject $object, $targets = null );
}

