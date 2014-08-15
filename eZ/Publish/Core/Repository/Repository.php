<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Limitation\Type as LimitationType;
use Exception;
use RuntimeException;

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
     * Currently logged in user object for permission purposes
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $currentUser;

    /**
     * Flag to specify if current execution is sudo mode, only set by {@see sudo()}.
     *
     * @var bool
     */
    private $sudoFlag = false;

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
     * Instance of name schema resolver service
     *
     * @var \eZ\Publish\Core\Repository\NameSchemaService
     */
    protected $nameSchemaService;

    /**
     * Instance of relation processor service
     *
     * @var \eZ\Publish\Core\Repository\RelationProcessor
     */
    protected $relationProcessor;

    /**
     * Instance of URL alias service
     *
     * @var \eZ\Publish\Core\Repository\URLAliasService
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
     * Instance of role service
     *
     * @var \eZ\Publish\Core\Repository\LimitationService
     */
    protected $limitationService;

    /**
     * Instance of domain mapper
     *
     * @var \eZ\Publish\Core\Repository\DomainMapper
     */
    protected $domainMapper;

    /**
     * Instance of permissions criterion handler
     *
     * @var \eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected $permissionsCriterionHandler;

    /**
     * Array of arrays of commit events indexed by the transaction count.
     *
     * @var array
     */
    protected $commitEventsQueue = array();

    /**
     * @var int
     */
    protected $transactionDepth = 0;

    /**
     * @var int
     */
    private $transactionCount = 0;

    /**

    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param array $serviceSettings
     * @param \eZ\Publish\API\Repository\Values\User\User|null $user
     */
    public function __construct( PersistenceHandler $persistenceHandler, array $serviceSettings = array(), User $user = null )
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->serviceSettings = $serviceSettings + array(
            'content' => array(),
            'contentType' => array(),
            'location' => array(),
            'section' => array(),
            'role' => array(),
            'user' => array(
                'anonymousUserID' => 10
            ),
            'language' => array(),
            'trash' => array(),
            'io' => array(),
            'objectState' => array(),
            'search' => array(),
            'fieldType' => array(),
            'urlAlias' => array(),
            'urlWildcard' => array(),
            'nameSchema' => array(),
            'languages' => array()
        );

        if ( !empty( $this->serviceSettings['languages'] ) )
        {
            $this->serviceSettings['language']['languages'] = $this->serviceSettings['languages'];
        }

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
        if ( !$this->currentUser instanceof User )
        {
            $this->currentUser = $this->getUserService()->loadUser(
                $this->serviceSettings["user"]["anonymousUserID"]
            );
        }

        return $this->currentUser;
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
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $this->currentUser = $user;
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
        if ( $this->sudoFlag === true )
            throw new RuntimeException( "Recursive sudo use detected, abort abort!" );

        $this->sudoFlag = true;
        try
        {
            $returnValue = $callback( $this );
        }
        catch ( Exception $e  )
        {
            $this->sudoFlag = false;
            throw $e;
        }

        $this->sudoFlag = false;
        return $returnValue;
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
        // Full access if sudoFlag is set by {@see sudo()}
        if ( $this->sudoFlag === true )
            return true;

        if ( $user === null )
            $user = $this->getCurrentUser();

        // Uses SPI to avoid triggering permission checks in Role/User service
        $permissionSets = array();
        $roleService = $this->getRoleService();
        $limitationService = $this->getLimitationService();
        $spiRoleAssignments = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId( $user->id, true );
        foreach ( $spiRoleAssignments as $spiRoleAssignment )
        {
            $permissionSet = array( 'limitation' => null, 'policies' => array() );

            $spiRole = $this->persistenceHandler->userHandler()->loadRole( $spiRoleAssignment->roleId );
            foreach ( $spiRole->policies as $spiPolicy )
            {
                if ( $spiPolicy->module === '*' && $spiRoleAssignment->limitationIdentifier === null )
                    return true;

                if ( $spiPolicy->module !== $module && $spiPolicy->module !== '*' )
                    continue;

                if ( $spiPolicy->function === '*' && $spiRoleAssignment->limitationIdentifier === null )
                    return true;

                if ( $spiPolicy->function !== $function && $spiPolicy->function !== '*' )
                    continue;

                if ( $spiPolicy->limitations === '*' && $spiRoleAssignment->limitationIdentifier === null )
                    return true;

                $permissionSet['policies'][] = $roleService->buildDomainPolicyObject( $spiPolicy );
            }

            if ( !empty( $permissionSet['policies'] ) )
            {
                if ( $spiRoleAssignment->limitationIdentifier !== null )
                    $permissionSet['limitation'] = $limitationService
                        ->getLimitationType( $spiRoleAssignment->limitationIdentifier )
                        ->buildValue( $spiRoleAssignment->values );

                $permissionSets[] = $permissionSet;
            }
        }

        if ( !empty( $permissionSets ) )
            return $permissionSets;

        return false;// No policies matching $module and $function, or they contained limitations
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
        $permissionSets = $this->hasAccess( $module, $function );
        if ( $permissionSets === false || $permissionSets === true )
        {
            return $permissionSets;
        }

        if ( $targets instanceof ValueObject )
        {
            $targets = array( $targets );
        }
        else if ( $targets !== null && !is_array( $targets ) )
        {
            throw new InvalidArgumentType(
                "\$targets",
                "null|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject[]",
                $targets
            );
        }

        $limitationService = $this->getLimitationService();
        $currentUser = $this->getCurrentUser();
        foreach ( $permissionSets as $permissionSet )
        {
            /**
             * First deal with Role limitation if any
             *
             * Here we accept ACCESS_GRANTED and ACCESS_ABSTAIN, the latter in cases where $object and $targets
             * are not supported by limitation.
             *
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[] $permissionSet
             */
            if ( $permissionSet['limitation'] instanceof Limitation )
            {
                $type = $limitationService->getLimitationType( $permissionSet['limitation']->getIdentifier() );
                $accessVote = $type->evaluate( $permissionSet['limitation'], $currentUser, $object, $targets );
                if ( $accessVote === LimitationType::ACCESS_DENIED )
                    continue;
            }

            /**
             * Loop over all policies
             *
             * These are already filtered by hasAccess and given hasAccess did not return boolean
             * there must be some, so only return true if one of them says yes.
             *
             * @var \eZ\Publish\API\Repository\Values\User\Policy $policy
             */
            foreach ( $permissionSet['policies'] as $policy )
            {
                $limitations = $policy->getLimitations();

                /**
                 * Return true if policy gives full access (aka no limitations)
                 */
                if ( $limitations === '*' )
                    return true;

                /**
                 * Loop over limitations, all must return ACCESS_GRANTED for policy to pass.
                 * If limitations was empty array this means same as '*'
                 */
                $limitationsPass = true;
                foreach ( $limitations as $limitation )
                {
                    $type = $limitationService->getLimitationType( $limitation->getIdentifier() );
                    $accessVote = $type->evaluate( $limitation, $currentUser, $object, $targets );
                    /**
                     * For policy limitation atm only support ACCESS_GRANTED
                     *
                     * Reasoning: Right now, use of a policy limitation not valid for a policy is per definition a
                     * BadState. To reach this you would have to configure the "limitationMap" wrongly, like using
                     * Node (Location) limitation on state/assign. So in this case Role Limitations will return
                     * ACCESS_ABSTAIN (== no access here), and other limitations will throw InvalidArgument above,
                     * both cases forcing dev to investigate to find miss configuration. This might be relaxed in
                     * the future if valid use cases for ACCESS_ABSTAIN on policy limitations becomes known.
                     */
                    if ( $accessVote !== LimitationType::ACCESS_GRANTED )
                    {
                        $limitationsPass = false;
                        break;// Break to next policy, all limitations must pass
                    }
                }
                if ( $limitationsPass )
                    return true;
            }
        }
        return false;// None of the limitation sets wanted to let you in, sorry!
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

        $this->contentService = new ContentService(
            $this,
            $this->persistenceHandler,
            $this->getDomainMapper(),
            $this->getRelationProcessor(),
            $this->getNameSchemaService(),
            $this->serviceSettings['content']
        );
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

        $this->languageService = new LanguageService(
            $this,
            $this->persistenceHandler->contentLanguageHandler(),
            $this->serviceSettings['language']
        );
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

        $this->contentTypeService = new ContentTypeService(
            $this,
            $this->persistenceHandler->contentTypeHandler(),
            $this->getDomainMapper(),
            $this->serviceSettings['contentType']
        );
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

        $this->locationService = new LocationService(
            $this,
            $this->persistenceHandler,
            $this->getDomainMapper(),
            $this->getNameSchemaService(),
            $this->getPermissionsCriterionHandler(),
            $this->serviceSettings['location']
        );
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

        $this->trashService = new TrashService(
            $this,
            $this->persistenceHandler,
            $this->getNameSchemaService(),
            $this->serviceSettings['trash']
        );
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

        $this->sectionService = new SectionService(
            $this,
            $this->persistenceHandler->sectionHandler(),
            $this->serviceSettings['section']
        );
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

        $this->userService = new UserService(
            $this,
            $this->persistenceHandler->userHandler(),
            $this->serviceSettings['user']
        );
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

        $this->urlAliasService = new URLAliasService(
            $this,
            $this->persistenceHandler->urlAliasHandler(),
            $this->serviceSettings['urlAlias']
        );
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

        $this->urlWildcardService = new URLWildcardService(
            $this,
            $this->persistenceHandler->urlWildcardHandler(),
            $this->serviceSettings['urlWildcard']
        );
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

        $this->objectStateService = new ObjectStateService(
            $this, $this->persistenceHandler->objectStateHandler(),
            $this->serviceSettings['objectState']
        );
        return $this->objectStateService;
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

        $this->roleService = new RoleService(
            $this,
            $this->persistenceHandler->userHandler(),
            $this->getLimitationService(),
            $this->serviceSettings['role']
        );
        return $this->roleService;
    }

    /**
     * Get LimitationService
     *
     * @return \eZ\Publish\Core\Repository\LimitationService
     */
    protected function getLimitationService()
    {
        if ( $this->limitationService !== null )
            return $this->limitationService;

        $this->limitationService = new LimitationService( $this->serviceSettings['role'] );
        return $this->limitationService;
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

        $this->searchService = new SearchService(
            $this,
            $this->persistenceHandler->searchHandler(),
            $this->persistenceHandler->locationSearchHandler(),
            $this->getDomainMapper(),
            $this->getPermissionsCriterionHandler(),
            $this->serviceSettings['search']
        );
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

        $this->fieldTypeService = new FieldTypeService( $this->serviceSettings['fieldType'] );
        return $this->fieldTypeService;
    }

    /**
     * Get NameSchemaResolverService
     *
     * @access private Internal service for the Core Services
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\NameSchemaService
     */
    protected function getNameSchemaService()
    {
        if ( $this->nameSchemaService !== null )
            return $this->nameSchemaService;

        $this->nameSchemaService = new NameSchemaService( $this, $this->serviceSettings['nameSchema'] );
        return $this->nameSchemaService;
    }

    /**
     * Get RelationProcessor
     *
     * @access private Internal service for the Core Services
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\RelationProcessor
     */
    protected function getRelationProcessor()
    {
        if ( $this->relationProcessor !== null )
            return $this->relationProcessor;

        $this->relationProcessor = new RelationProcessor( $this, $this->persistenceHandler );
        return $this->relationProcessor;
    }

    /**
     * Get RelationProcessor
     *
     * @access private Internal service for the Core Services
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\DomainMapper
     */
    protected function getDomainMapper()
    {
        if ( $this->domainMapper !== null )
            return $this->domainMapper;

        $this->domainMapper = new DomainMapper(
            $this,
            $this->persistenceHandler->contentTypeHandler(),
            $this->persistenceHandler->contentLanguageHandler()
        );
        return $this->domainMapper;
    }

    /**
     * Get PermissionsCriterionHandler
     *
     * @access private Internal service for the Core Services
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected function getPermissionsCriterionHandler()
    {
        return $this->permissionsCriterionHandler !== null ?
            $this->permissionsCriterionHandler :
            $this->permissionsCriterionHandler = new PermissionsCriterionHandler( $this );
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

        ++$this->transactionDepth;
        $this->commitEventsQueue[++$this->transactionCount] = array();
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

            --$this->transactionDepth;

            if ( $this->transactionDepth === 0 )
            {
                $queueCountDown = count( $this->commitEventsQueue );
                foreach ( $this->commitEventsQueue as $eventsQueue )
                {
                    --$queueCountDown;
                    if ( empty( $eventsQueue ) )
                        continue;

                    $eventCountDown = count( $eventsQueue );
                    foreach ( $eventsQueue as $event )
                    {
                        --$eventCountDown;
                        // event expects a boolean param, if true it means it is last event (for commit use)
                        $event( $queueCountDown === 0 && $eventCountDown === 0 );
                    }
                }

                $this->commitEventsQueue = array();
            }
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

            --$this->transactionDepth;
            unset( $this->commitEventsQueue[$this->transactionCount] );
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage(), 0, $e );
        }
    }

    /**
     * Enqueue an event to be triggered at commit or directly if no transaction has started
     *
     * @param Callable $event
     */
    public function commitEvent( $event )
    {
        if ( $this->transactionDepth !== 0 )
        {
            $this->commitEventsQueue[$this->transactionCount][] = $event;
        }
        else
        {
            // event expects a boolean param, if true it means it is last event (for commit use)
            $event( true );
        }
    }

    /**
     * Only for internal use.
     *
     * Creates a \DateTime object for $timestamp in the current time zone
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    public function createDateTime( $timestamp = null )
    {
        $dateTime = new \DateTime();
        if ( $timestamp !== null )
        {
            $dateTime->setTimestamp( $timestamp );
        }
        return $dateTime;
    }
}
