<?php
/**
 * File containing the eZ\Publish\API\Repository\Repository class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Repository interface
 * @package eZ\Publish\API\Repository
 */
interface Repository
{
    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser();

    /**
     *
     * sets the current user to the user with the given user id
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function setCurrentUser( User $user );

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null );

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\ValueObject $value
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target
     */
    public function canUser( $module, $function, ValueObject $value, ValueObject $target = null );

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService();

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService();

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService();

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService();

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService();

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService();

    /**
     * Get User Service
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService();

    /**
     * Get IO Service
     *
     * Get service object to perform operations on binary files
     *
     * @return \eZ\Publish\API\Repository\IOService
     */
    public function getIOService();

    /**
     * Get RoleService
     * 
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService();
    
    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction();

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit();

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback();
}

