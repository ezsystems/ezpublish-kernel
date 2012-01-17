<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;


use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\User\User;

/**
 * Repository class
 * @package ezp\PublicAPI\Interfaces
 */
interface Repository
{

    /**
     * Get current user
     *
     * @return User
     */
    public function getUser();

    /**
     *
     * sets the current user to the user with the given user id
     * @param User $user
     */
    public function setCurrentUser( /*User*/ $user );

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param \ezp\PublicAPI\Values\User\User $user
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess($module, $function, User $user = null);

    /**
     *
     * indicates if the current user is allowed to perform an action given by the function on the given
     * objects,
     * @param string $module
     * @param string $function
     * @param ValueObject $value
     * @param ValueObject $target
     */
    public function canUser($module,$function,ValueObject $value, ValueObject $target);

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     *
     * @return ContentService
     */
    public function getContentService();

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return LanguageService
     */
    public function getContentLanguageService();

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return ContentTypeService
     */
    public function getContentTypeService();

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return LocationService
     */
    public function getLocationService();

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return TrashService
     */
    public function getTrashService();

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return SectionService
     */
    public function getSectionService();

    /**
     * Get User Service
     *
     * Get service object to perform operations on User objects and it's aggregate members.
     * ( UserGroups, UserRole, UserRolePolicy & UserRolePolicyLimitation )
     *
     * @return UserService
     */
    public function getUserService();


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

