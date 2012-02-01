<?php
/**
 * @package eZ\Publish\API\Interfaces
 */
namespace eZ\Publish\API\Interfaces;


use eZ\Publish\API\Values\ValueObject;

use eZ\Publish\API\Values\User\User;

/**
 * Repository class
 * @package eZ\Publish\API\Interfaces
 */
interface Repository
{

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Values\User\User
     */
    public function getCurrentUser();

    /**
     *
     * sets the current user to the user with the given user id
     * @param \eZ\Publish\API\Values\User\User $user
     */
    public function setCurrentUser( User $user );

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Values\User\User $user
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null );

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Values\ValueObject $value
     * @param \eZ\Publish\API\Values\ValueObject $target
     */
    public function canUser( $module, $function, ValueObject $value, ValueObject $target );

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     *
     * @return \eZ\Publish\API\Interfaces\ContentService
     */
    public function getContentService();

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Interfaces\LanguageService
     */
    public function getContentLanguageService();

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Interfaces\ContentTypeService
     */
    public function getContentTypeService();

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Interfaces\LocationService
     */
    public function getLocationService();

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Interfaces\TrashService
     */
    public function getTrashService();

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Interfaces\SectionService
     */
    public function getSectionService();

    /**
     * Get User Service
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Interfaces\UserService
     */
    public function getUserService();

    /**
     * Get RoleService
     * 
     * @return \eZ\Publish\API\Interfaces\RoleService
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
     * @throws RuntimeException If no transaction has been started
     */
    public function commit();

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function rollback();
}

