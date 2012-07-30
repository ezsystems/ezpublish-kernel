<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;
use eZ\Publish\Core\Base\Exceptions\BadConfiguration,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\SPI\IO\Handler as IoHandler,
    eZ\Publish\SPI\Persistence\Handler as PersistenceHandler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\Core\Repository\ContentService,
    eZ\Publish\Core\Repository\LanguageService,
    eZ\Publish\Core\Repository\TrashService,
    eZ\Publish\Core\Repository\LocationService,
    eZ\Publish\Core\Repository\SectionService,
    eZ\Publish\Core\Repository\ContentTypeService,
    eZ\Publish\Core\Repository\RoleService,
    eZ\Publish\Core\Repository\UserService,
    eZ\Publish\Core\Repository\IOService,
    eZ\Publish\Core\Repository\ObjectStateService,
    eZ\Publish\Core\Repository\URLAliasService,
    eZ\Publish\Core\Repository\URLWildcardService,
    eZ\Publish\API\Repository\Values\ValueObject,
    eZ\Publish\API\Repository\Values\User\User,
    Exception,
    LogicException,
    RuntimeException;

/**
 * Repository class
 * @package eZ\Publish\Core\Repository
 */
class Repository implements RepositoryInterface
{
    /**
     * Repository Handler object
     *
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Io Handler object
     *
     * @var \eZ\Publish\SPI\IO\Handler
     */
    protected $ioHandler;

    /**
     * Currently logged in user object for permission purposes
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

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
     * Instance of IO service
     *
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $ioService;

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
     * Instance of object state service
     *
     * @var \eZ\Publish\Core\Repository\ValidatorService
     */
    protected $validatorService;

    /**
     * Instance of URL alias service
     *
     * @var \eZ\Publish\Core\Repository\UrlAliasService
     */
    protected $urlAliasService;

    /**
     * Instance of URL wildcard service
     *
     * @var \eZ\Publish\Core\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Service settings, first level key is service name
     *
     * @var array
     */
    protected $serviceSettings;

    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\IO\Handler $ioHandler
     * @param array $serviceSettings
     * @param \eZ\Publish\API\Repository\Values\User\User|null $user
     */
    public function __construct( PersistenceHandler $persistenceHandler, IoHandler $ioHandler, array $serviceSettings = array(), User $user = null )
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->ioHandler = $ioHandler;
        $this->serviceSettings = $serviceSettings + array(
            'content' => array(),
            'contentType' => array(),
            'location' => array(),
            'section' => array(),
            'role' => array(),
            'user' => array(),
            'language' => array(),
            'trash' => array(),
            'io' => array(),
            'objectState' => array(),
            'search' => array(),
            'fieldType' => array(),
            'urlAlias' => array(),
            'urlWildcard' => array(),
        );

        if ( $user !== null )
            $this->setCurrentUser( $user );
    }

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        if ( !$this->user instanceof User )
            $this->user = $this->getUserService()->loadAnonymousUser();

        return $this->user;
    }

    /**
     *
     * sets the current user to the user with the given user id
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function setCurrentUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $oldUser = $this->user;
        $this->user = $user;
        return $oldUser;
    }

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        //@todo implement, see impl in ezp-next
    }

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\ValueObject $value
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target
     * @return array|bool
     */
    public function canUser( $module, $function, ValueObject $value, ValueObject $target = null )
    {
        $className = $value;
        $limitationArray = $this->hasAccess( $module, $function );
        if ( $limitationArray === false || $limitationArray === true )
        {
            return $limitationArray;
        }
        if ( empty( $definition['functions'][$function] ) )
        {
            throw new BadConfiguration(
                "{$className}::definition()",
                "function limitations returned for '{$function}', but none defined in definition()"
            );
        }

        /**
         * @todo Somewhere to get limitation logic from (functions), then $value should impl a interface
         * that tells us where to get it from for instance.
         * @var array $functions
         */
        $functions = $value::getLimitationFunctions();
        foreach ( $limitationArray as $limitationSet )
        {
            $limitationSetSaysYes = true;
            foreach ( $limitationSet as $limitationKey => $limitationValues )
            {
                if ( !isset( $functions[$function][$limitationKey]['compare'] ) )
                {
                    throw new LogicException(
                        "\$definition[functions][{$function}][{$limitationKey}][compare] logic error, " .
                        "could not find limitation compare function on {$className}::definition()"
                    );
                }

                $limitationCompareFn = $functions[$function][$limitationKey]['compare'];
                if ( !is_callable( $limitationCompareFn ) )
                {
                    throw new LogicException(
                        "\$definition[functions][{$function}][{$limitationKey}][compare] logic error, " .
                        "compare function from {$className}::definition() is not callable"
                    );
                }

                if ( !$limitationCompareFn( $value, $limitationValues, $this, $target ) )
                {
                    $limitationSetSaysYes = false;
                    // Break to next limitationSet
                    break;
                    // If needed, there could be a if condition here building up an array of all limitations
                    // that are denying user access
                }
            }
            if ( $limitationSetSaysYes )
                return true;
        }
        return false;
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

        $this->contentService = new ContentService( $this, $this->persistenceHandler, $this->serviceSettings['content'] );
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
        if ( $this->languageService !== null )
            return $this->languageService;

        $this->languageService = new LanguageService( $this, $this->persistenceHandler, $this->serviceSettings['language'] );
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
        if ( $this->contentTypeService !== null )
            return $this->contentTypeService;

        $this->contentTypeService = new ContentTypeService( $this, $this->persistenceHandler, $this->serviceSettings['contentType'] );
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
        if ( $this->locationService !== null )
            return $this->locationService;

        $this->locationService = new LocationService( $this, $this->persistenceHandler, $this->serviceSettings['location'] );
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
        if ( $this->trashService !== null )
            return $this->trashService;

        $this->trashService = new TrashService( $this, $this->persistenceHandler, $this->serviceSettings['trash'] );
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
        if ( $this->sectionService !== null )
            return $this->sectionService;

        $this->sectionService = new SectionService( $this, $this->persistenceHandler, $this->serviceSettings['section'] );
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
        if ( $this->userService !== null )
            return $this->userService;

        $this->userService = new UserService( $this, $this->persistenceHandler, $this->serviceSettings['user'] );
        return $this->userService;
    }

    /**
     * Get URLAliasService
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService()
    {
        if ( $this->urlAliasService !== null )
            return $this->urlAliasService;

        $this->urlAliasService = new URLAliasService( $this, $this->persistenceHandler, $this->serviceSettings['urlAlias'] );
        return $this->urlAliasService;
    }

    /**
     * Get URLWildcardService
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService()
    {
        if ( $this->urlWildcardService !== null )
            return $this->urlWildcardService;

        $this->urlWildcardService = new URLWildcardService( $this, $this->persistenceHandler, $this->serviceSettings['urlWildcard'] );
        return $this->urlWildcardService;
    }

    /**
     * Get ObjectStateService
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService()
    {
        if ( $this->objectStateService !== null )
            return $this->objectStateService;

        $this->objectStateService = new ObjectStateService( $this, $this->persistenceHandler, $this->serviceSettings['objectState'] );
        return $this->objectStateService;
    }

    /**
     * Get IO Service
     *
     * Get service object to perform operations on binary files
     *
     * @return \eZ\Publish\API\Repository\IOService
     */
    public function getIOService()
    {
        if ( $this->ioService !== null )
            return $this->ioService;

        $this->ioService = new IOService( $this, $this->ioHandler, $this->serviceSettings['io'] );
        return $this->ioService;
    }

    /**
     * Get RoleService
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if ( $this->roleService !== null )
            return $this->roleService;

        $this->roleService = new RoleService( $this, $this->persistenceHandler, $this->serviceSettings['role'] );
        return $this->roleService;
    }

    /**
     * Get SearchService
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        if ( $this->searchService !== null )
            return $this->searchService;

        $this->searchService = new SearchService( $this, $this->persistenceHandler, $this->serviceSettings['search'] );
        return $this->searchService;
    }

    /**
     * Get FieldTypeService
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService()
    {
        if ( $this->fieldTypeService !== null )
            return $this->fieldTypeService;

        $this->fieldTypeService = new FieldTypeService( $this, $this->persistenceHandler, $this->serviceSettings['fieldType'] );
        return $this->fieldTypeService;
    }

    /**
     * Get ValidatorService
     *
     * @return \eZ\Publish\Core\Repository\ValidatorService
     */
    public function getValidatorService()
    {
        if ( $this->validatorService !== null )
            return $this->validatorService;

        $this->validatorService = new ValidatorService();
        return $this->validatorService;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->persistenceHandler->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function commit()
    {
        try
        {
            $this->persistenceHandler->commit();
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage(), 0, $e );
        }
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        try
        {
            $this->persistenceHandler->rollback();
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage(), 0, $e );
        }
    }
}
