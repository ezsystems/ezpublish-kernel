<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Permission;

/*
use eZ\Publish\API\Repository\Values\User\Limitation;
use Exception;
use RuntimeException;
*/
use eZ\Publish\API\Repository\PermissionRepository as PermissionRepositoryInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Repository class
 * @package eZ\Publish\Core\Repository\Permission
 */
class Repository implements PermissionRepositoryInterface
{
    /**
     * Repository Handler object
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $innerRepository;

    /**
     * @var PermissionsService
     */
    protected $permissionsService;

    /**
     * Instance of content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Instance of section service
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Instance of role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Instance of search service
     *
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * Instance of user service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Instance of language service
     *
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * Instance of location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Instance of Trash service
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $trashService;

    /**
     * Instance of content type service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Instance of object state service
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Instance of field type service
     *
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /**
     * Instance of URL alias service
     *
     * @var \eZ\Publish\API\Repository\UrlAliasService
     */
    protected $urlAliasService;

    /**
     * Instance of URL wildcard service
     *
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Constructor
     *
     * Construct repository object from aggregated repository and permission
     * service
     *
     *
     * @param \eZ\Publish\API\Repository\Repository $innerRepository
     * @param PermissionsService $permissionsService
     */
    public function __construct( RepositoryInterface $innerRepository, PermissionsService $permissionsService )
    {
        $this->innerRepository = $innerRepository;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        return $this->permissionsService->getCurrentUser();
    }

    /**
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return void
     */
    public function setCurrentUser( User $user )
    {
        $this->permissionsService->setCurrentUser( $user );
    }

    /**
     * Allows API execution to be performed with full access sand-boxed
     *
     * The closure sandbox will do a catch all on exceptions and rethrow after
     * re-setting the sudo flag.
     *
     * Example use:
     *     $location = $repository->sudo(
     *         function ( $repo ) use ( $locationId )
     *         {
     *             return $repo->getLocationService()->loadLocation( $locationId )
     *         }
     *     );
     *
     * @access private This function is not official API atm, and can change anytime.
     *
     * @param \Closure $callback
     *
     * @throws \RuntimeException Thrown on recursive sudo() use.
     * @throws \Exception Re throws exceptions thrown inside $callback
     * @return mixed
     */
    public function sudo( \Closure $callback )
    {
        return $this->innerRepository->sudo( $callback );
    }

    /**
     * Check if user has access to a given module / function
     *
     * Low level function, use canUser instead if you have objects to check against.
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return boolean|array Bool if user has full or no access, array if limitations if not
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        return $this->permissionsService->hasAccess( $module, $function, $user );
    }

    /**
     * Check if user has access to a given action on a given value object
     *
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects.
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
    public function canUser( $module, $function, ValueObject $object, $targets = null )
    {
        return $this->permissionsService->canUser( $module, $function, $object, $targets );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService()
    {
        if ( $this->contentService !== null )
            return $this->contentService;

        $this->contentService = new ContentService( $this->innerRepository->getContentService(), $this->permissionsService );
        return $this->contentService;
    }

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService()
    {
        return $this->innerRepository->getContentLanguageService();
        if ( $this->languageService !== null )
            return $this->languageService;

        $this->languageService = new LanguageService( $this->innerRepository->getContentLanguageService(), $this->signalDispatcher );
        return $this->languageService;
    }

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService()
    {
        return $this->innerRepository->getContentTypeService();
        if ( $this->contentTypeService !== null )
            return $this->contentTypeService;

        $this->contentTypeService = new ContentTypeService( $this->innerRepository->getContentTypeService(), $this->signalDispatcher );
        return $this->contentTypeService;
    }

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService()
    {
        return $this->innerRepository->getLocationService();
        if ( $this->locationService !== null )
            return $this->locationService;

        $this->locationService = new LocationService( $this->innerRepository->getLocationService(), $this->signalDispatcher );
        return $this->locationService;
    }

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService()
    {
        return $this->innerRepository->getTrashService();
        if ( $this->trashService !== null )
            return $this->trashService;

        $this->trashService = new TrashService( $this->innerRepository->getTrashService(), $this->signalDispatcher );
        return $this->trashService;
    }

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService()
    {
        return $this->innerRepository->getSectionService();
        if ( $this->sectionService !== null )
            return $this->sectionService;

        $this->sectionService = new SectionService( $this->innerRepository->getSectionService(), $this->signalDispatcher );
        return $this->sectionService;
    }

    /**
     * Get User Service
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService()
    {
        return $this->innerRepository->getUserService();
        if ( $this->userService !== null )
            return $this->userService;

        $this->userService = new UserService( $this->innerRepository->getUserService(), $this->signalDispatcher );
        return $this->userService;
    }

    /**
     * Get URLAliasService
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService()
    {
        return $this->innerRepository->getURLAliasService();
        if ( $this->urlAliasService !== null )
            return $this->urlAliasService;

        $this->urlAliasService = new URLAliasService( $this->innerRepository->getURLAliasService(), $this->signalDispatcher );
        return $this->urlAliasService;
    }

    /**
     * Get URLWildcardService
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService()
    {
        return $this->innerRepository->getURLWildcardService();
        if ( $this->urlWildcardService !== null )
            return $this->urlWildcardService;

        $this->urlWildcardService = new URLWildcardService( $this->innerRepository->getURLWildcardService(), $this->signalDispatcher );
        return $this->urlWildcardService;
    }

    /**
     * Get ObjectStateService
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService()
    {
        return $this->innerRepository->getObjectStateService();
        if ( $this->objectStateService !== null )
            return $this->objectStateService;

        $this->objectStateService = new ObjectStateService( $this->innerRepository->getObjectStateService(), $this->signalDispatcher );
        return $this->objectStateService;
    }

    /**
     * Get RoleService
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        return $this->innerRepository->getRoleService();
        if ( $this->roleService !== null )
            return $this->roleService;

        $this->roleService = new RoleService( $this->innerRepository->getRoleService(), $this->signalDispatcher );
        return $this->roleService;
    }

    /**
     * Get SearchService
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        return $this->innerRepository->getSearchService();
        if ( $this->searchService !== null )
            return $this->searchService;

        $this->searchService = new SearchService( $this->innerRepository->getSearchService(), $this->signalDispatcher );
        return $this->searchService;
    }

    /**
     * Get FieldTypeService
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService()
    {
        return $this->innerRepository->getFieldTypeService();
        if ( $this->fieldTypeService !== null )
            return $this->fieldTypeService;

        $this->fieldTypeService = new FieldTypeService( $this->innerRepository->getFieldTypeService(), $this->signalDispatcher );
        return $this->fieldTypeService;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        return $this->innerRepository->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit()
    {
        return $this->innerRepository->commit();
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        return $this->innerRepository->rollback();
    }

    /**
     * Enqueue an event to be triggered at commit or directly if no transaction has started
     *
     * @param Callable $event
     */
    public function commitEvent( $event )
    {
        $this->innerRepository->commitEvent( $event );
    }
}