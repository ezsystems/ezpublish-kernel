<?php

/**
 * Repository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Repository\Permission\CachedPermissionService;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use Exception;
use RuntimeException;

/**
 * Repository class.
 */
class Repository implements RepositoryInterface
{
    /**
     * Repository Handler object.
     *
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Instance of main Search Handler.
     *
     * @var \eZ\Publish\SPI\Search\Handler
     */
    protected $searchHandler;

    /**
     * @deprecated since 6.6, to be removed. Current user handling is moved to PermissionResolver.
     *
     * Currently logged in user object if already loaded.
     *
     * @var \eZ\Publish\API\Repository\Values\User\User|null
     */
    protected $currentUser;

    /**
     * @deprecated since 6.6, to be removed. Current user handling is moved to PermissionResolver.
     *
     * Currently logged in user reference for permission purposes.
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserReference
     */
    protected $currentUserRef;

    /**
     * Instance of content service.
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Instance of section service.
     *
     * @var \eZ\Publish\API\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Instance of role service.
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Instance of search service.
     *
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * Instance of user service.
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Instance of language service.
     *
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * Instance of location service.
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Instance of Trash service.
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $trashService;

    /**
     * Instance of content type service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Instance of object state service.
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Instance of field type service.
     *
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /**
     * Instance of FieldTypeRegistry.
     *
     * @var \eZ\Publish\Core\Repository\Helper\FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    /**
     * Instance of NameableFieldTypeRegistry.
     *
     * @var \eZ\Publish\Core\Repository\Helper\NameableFieldTypeRegistry
     */
    private $nameableFieldTypeRegistry;

    /**
     * Instance of name schema resolver service.
     *
     * @var \eZ\Publish\Core\Repository\Helper\NameSchemaService
     */
    protected $nameSchemaService;

    /**
     * Instance of relation processor service.
     *
     * @var \eZ\Publish\Core\Repository\Helper\RelationProcessor
     */
    protected $relationProcessor;

    /**
     * Instance of URL alias service.
     *
     * @var \eZ\Publish\Core\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Instance of URL wildcard service.
     *
     * @var \eZ\Publish\Core\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Service settings, first level key is service name.
     *
     * @var array
     */
    protected $serviceSettings;

    /**
     * Instance of role service.
     *
     * @var \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    protected $limitationService;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\RoleDomainMapper
     */
    protected $roleDomainMapper;

    /**
     * Instance of domain mapper.
     *
     * @var \eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected $domainMapper;

    /**
     * Instance of content type domain mapper.
     *
     * @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper
     */
    protected $contentTypeDomainMapper;

    /**
     * Instance of permissions-resolver and -criterion resolver.
     *
     * @var \eZ\Publish\API\Repository\PermissionCriterionResolver|\eZ\Publish\API\Repository\PermissionResolver
     */
    protected $permissionsHandler;

    /**
     * @var \eZ\Publish\Core\Search\Common\BackgroundIndexer|null
     */
    protected $backgroundIndexer;

    /**
     * Constructor.
     *
     * Construct repository object with provided storage engine
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param array $serviceSettings
     * @param \eZ\Publish\API\Repository\Values\User\UserReference|null $user
     */
    public function __construct(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        array $serviceSettings = array(),
        APIUserReference $user = null
    ) {
        $this->persistenceHandler = $persistenceHandler;
        $this->searchHandler = $searchHandler;
        $this->backgroundIndexer = $backgroundIndexer;
        $this->serviceSettings = $serviceSettings + array(
            'content' => array(),
            'contentType' => array(),
            'location' => array(),
            'section' => array(),
            'role' => array(),
            'user' => array(
                'anonymousUserID' => 10,
            ),
            'language' => array(),
            'trash' => array(),
            'io' => array(),
            'objectState' => array(),
            'search' => array(),
            'fieldType' => array(),
            'nameableFieldTypes' => array(),
            'urlAlias' => array(),
            'urlWildcard' => array(),
            'nameSchema' => array(),
            'languages' => array(),
        );

        if (!empty($this->serviceSettings['languages'])) {
            $this->serviceSettings['language']['languages'] = $this->serviceSettings['languages'];
        }

        if ($user instanceof User) {
            $this->currentUser = $user;
            $this->currentUserRef = new UserReference($user->getUserId());
        } elseif ($user instanceof APIUserReference) {
            $this->currentUserRef = $user;
        } else {
            $this->currentUserRef = new UserReference($this->serviceSettings['user']['anonymousUserID']);
        }
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user.
     *
     * Loads the full user object if not already loaded, if you only need to know user id use {@see getCurrentUserReference()}
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        if ($this->currentUser === null) {
            $this->currentUser = $this->getUserService()->loadUser(
                $this->getPermissionResolver()->getCurrentUserReference()->getUserId()
            );
        }

        return $this->currentUser;
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::getCurrentUserReference() instead.
     *
     * Get current user reference.
     *
     * @since 5.4.5
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getCurrentUserReference()
    {
        return $this->getPermissionResolver()->getCurrentUserReference();
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::setCurrentUserReference() instead.
     *
     * Sets the current user to the given $user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @throws InvalidArgumentValue If UserReference does not contain a id
     */
    public function setCurrentUser(APIUserReference $user)
    {
        $id = $user->getUserId();
        if (!$id) {
            throw new InvalidArgumentValue('$user->getUserId()', $id);
        }

        if ($user instanceof User) {
            $this->currentUser = $user;
            $this->currentUserRef = new UserReference($id);
        } else {
            $this->currentUser = null;
            $this->currentUserRef = $user;
        }

        return $this->getPermissionResolver()->setCurrentUserReference($this->currentUserRef);
    }

    /**
     * Allows API execution to be performed with full access sand-boxed.
     *
     * The closure sandbox will do a catch all on exceptions and rethrow after
     * re-setting the sudo flag.
     *
     * Example use:
     *     $location = $repository->sudo(
     *         function ( Repository $repo ) use ( $locationId )
     *         {
     *             return $repo->getLocationService()->loadLocation( $locationId )
     *         }
     *     );
     *
     *
     * @param \Closure $callback
     * @param \eZ\Publish\API\Repository\Repository $outerRepository
     *
     * @throws \RuntimeException Thrown on recursive sudo() use.
     * @throws \Exception Re throws exceptions thrown inside $callback
     *
     * @return mixed
     */
    public function sudo(\Closure $callback, RepositoryInterface $outerRepository = null)
    {
        return $this->getPermissionResolver()->sudo(
            $callback,
            $outerRepository !== null ? $outerRepository : $this
        );
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::hasAccess() instead.
     *
     * Check if user has access to a given module / function.
     *
     * Low level function, use canUser instead if you have objects to check against.
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return bool|array Bool if user has full or no access, array if limitations if not
     */
    public function hasAccess($module, $function, APIUserReference $user = null)
    {
        return $this->getPermissionResolver()->hasAccess($module, $function, $user);
    }

    /**
     * @deprecated since 6.6, to be removed. Use PermissionResolver::canUser() instead.
     *
     * Check if user has access to a given action on a given value object.
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
     * @return bool
     */
    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        if ($targets instanceof ValueObject) {
            $targets = array($targets);
        } elseif ($targets === null) {
            $targets = [];
        } elseif (!is_array($targets)) {
            throw new InvalidArgumentType(
                '$targets',
                'null|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject|\\eZ\\Publish\\API\\Repository\\Values\\ValueObject[]',
                $targets
            );
        }

        return $this->getPermissionResolver()->canUser($module, $function, $object, $targets);
    }

    /**
     * Get Content Service.
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService()
    {
        if ($this->contentService !== null) {
            return $this->contentService;
        }

        $this->contentService = new ContentService(
            $this,
            $this->persistenceHandler,
            $this->getDomainMapper(),
            $this->getRelationProcessor(),
            $this->getNameSchemaService(),
            $this->getFieldTypeRegistry(),
            $this->serviceSettings['content']
        );

        return $this->contentService;
    }

    /**
     * Get Content Language Service.
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService()
    {
        if ($this->languageService !== null) {
            return $this->languageService;
        }

        $this->languageService = new LanguageService(
            $this,
            $this->persistenceHandler->contentLanguageHandler(),
            $this->serviceSettings['language']
        );

        return $this->languageService;
    }

    /**
     * Get Content Type Service.
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService()
    {
        if ($this->contentTypeService !== null) {
            return $this->contentTypeService;
        }

        $this->contentTypeService = new ContentTypeService(
            $this,
            $this->persistenceHandler->contentTypeHandler(),
            $this->getDomainMapper(),
            $this->getContentTypeDomainMapper(),
            $this->getFieldTypeRegistry(),
            $this->serviceSettings['contentType']
        );

        return $this->contentTypeService;
    }

    /**
     * Get Content Location Service.
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService()
    {
        if ($this->locationService !== null) {
            return $this->locationService;
        }

        $this->locationService = new LocationService(
            $this,
            $this->persistenceHandler,
            $this->getDomainMapper(),
            $this->getNameSchemaService(),
            $this->getPermissionCriterionResolver(),
            $this->serviceSettings['location']
        );

        return $this->locationService;
    }

    /**
     * Get Content Trash service.
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService()
    {
        if ($this->trashService !== null) {
            return $this->trashService;
        }

        $this->trashService = new TrashService(
            $this,
            $this->persistenceHandler,
            $this->getNameSchemaService(),
            $this->serviceSettings['trash']
        );

        return $this->trashService;
    }

    /**
     * Get Content Section Service.
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService()
    {
        if ($this->sectionService !== null) {
            return $this->sectionService;
        }

        $this->sectionService = new SectionService(
            $this,
            $this->persistenceHandler->sectionHandler(),
            $this->serviceSettings['section']
        );

        return $this->sectionService;
    }

    /**
     * Get User Service.
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService()
    {
        if ($this->userService !== null) {
            return $this->userService;
        }

        $this->userService = new UserService(
            $this,
            $this->persistenceHandler->userHandler(),
            $this->serviceSettings['user']
        );

        return $this->userService;
    }

    /**
     * Get URLAliasService.
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService()
    {
        if ($this->urlAliasService !== null) {
            return $this->urlAliasService;
        }

        $this->urlAliasService = new URLAliasService(
            $this,
            $this->persistenceHandler->urlAliasHandler(),
            $this->serviceSettings['urlAlias']
        );

        return $this->urlAliasService;
    }

    /**
     * Get URLWildcardService.
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService()
    {
        if ($this->urlWildcardService !== null) {
            return $this->urlWildcardService;
        }

        $this->urlWildcardService = new URLWildcardService(
            $this,
            $this->persistenceHandler->urlWildcardHandler(),
            $this->serviceSettings['urlWildcard']
        );

        return $this->urlWildcardService;
    }

    /**
     * Get ObjectStateService.
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService()
    {
        if ($this->objectStateService !== null) {
            return $this->objectStateService;
        }

        $this->objectStateService = new ObjectStateService(
            $this,
            $this->persistenceHandler->objectStateHandler(),
            $this->serviceSettings['objectState']
        );

        return $this->objectStateService;
    }

    /**
     * Get RoleService.
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if ($this->roleService !== null) {
            return $this->roleService;
        }

        $this->roleService = new RoleService(
            $this,
            $this->persistenceHandler->userHandler(),
            $this->getLimitationService(),
            $this->getRoleDomainMapper(),
            $this->serviceSettings['role']
        );

        return $this->roleService;
    }

    /**
     * Get LimitationService.
     *
     * @return \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    protected function getLimitationService()
    {
        if ($this->limitationService !== null) {
            return $this->limitationService;
        }

        $this->limitationService = new Helper\LimitationService($this->serviceSettings['role']);

        return $this->limitationService;
    }

    /**
     * Get RoleDomainMapper.
     *
     * @return \eZ\Publish\Core\Repository\Helper\RoleDomainMapper
     */
    protected function getRoleDomainMapper()
    {
        if ($this->roleDomainMapper !== null) {
            return $this->roleDomainMapper;
        }

        $this->roleDomainMapper = new Helper\RoleDomainMapper($this->getLimitationService());

        return $this->roleDomainMapper;
    }

    /**
     * Get SearchService.
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        if ($this->searchService !== null) {
            return $this->searchService;
        }

        $this->searchService = new SearchService(
            $this,
            $this->searchHandler,
            $this->getDomainMapper(),
            $this->getPermissionCriterionResolver(),
            $this->backgroundIndexer,
            $this->serviceSettings['search']
        );

        return $this->searchService;
    }

    /**
     * Get FieldTypeService.
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService()
    {
        if ($this->fieldTypeService !== null) {
            return $this->fieldTypeService;
        }

        $this->fieldTypeService = new FieldTypeService($this->getFieldTypeRegistry());

        return $this->fieldTypeService;
    }

    /**
     * Get PermissionResolver.
     *
     * @return \eZ\Publish\API\Repository\PermissionResolver
     */
    public function getPermissionResolver()
    {
        return $this->getCachedPermissionsResolver();
    }

    /**
     * @return Helper\FieldTypeRegistry
     */
    protected function getFieldTypeRegistry()
    {
        if ($this->fieldTypeRegistry !== null) {
            return $this->fieldTypeRegistry;
        }

        $this->fieldTypeRegistry = new Helper\FieldTypeRegistry($this->serviceSettings['fieldType']);

        return $this->fieldTypeRegistry;
    }

    /**
     * @return Helper\NameableFieldTypeRegistry
     */
    protected function getNameableFieldTypeRegistry()
    {
        if ($this->nameableFieldTypeRegistry !== null) {
            return $this->nameableFieldTypeRegistry;
        }

        $this->nameableFieldTypeRegistry = new Helper\NameableFieldTypeRegistry($this->serviceSettings['nameableFieldTypes']);

        return $this->nameableFieldTypeRegistry;
    }

    /**
     * Get NameSchemaResolverService.
     *
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @internal
     * @private
     *
     * @return \eZ\Publish\Core\Repository\Helper\NameSchemaService
     */
    public function getNameSchemaService()
    {
        if ($this->nameSchemaService !== null) {
            return $this->nameSchemaService;
        }

        $this->nameSchemaService = new Helper\NameSchemaService(
            $this->persistenceHandler->contentTypeHandler(),
            $this->getContentTypeDomainMapper(),
            $this->getNameableFieldTypeRegistry(),
            $this->serviceSettings['nameSchema']
        );

        return $this->nameSchemaService;
    }

    /**
     * Get RelationProcessor.
     *
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\Helper\RelationProcessor
     */
    protected function getRelationProcessor()
    {
        if ($this->relationProcessor !== null) {
            return $this->relationProcessor;
        }

        $this->relationProcessor = new Helper\RelationProcessor($this->persistenceHandler);

        return $this->relationProcessor;
    }

    /**
     * Get Content Domain Mapper.
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected function getDomainMapper()
    {
        if ($this->domainMapper !== null) {
            return $this->domainMapper;
        }

        $this->domainMapper = new Helper\DomainMapper(
            $this->persistenceHandler->contentHandler(),
            $this->persistenceHandler->locationHandler(),
            $this->persistenceHandler->contentTypeHandler(),
            $this->persistenceHandler->contentLanguageHandler(),
            $this->getFieldTypeRegistry()
        );

        return $this->domainMapper;
    }

    /**
     * Get ContentType Domain Mapper.
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper
     */
    protected function getContentTypeDomainMapper()
    {
        if ($this->contentTypeDomainMapper !== null) {
            return $this->contentTypeDomainMapper;
        }

        $this->contentTypeDomainMapper = new Helper\ContentTypeDomainMapper(
            $this->persistenceHandler->contentLanguageHandler(),
            $this->getFieldTypeRegistry()
        );

        return $this->contentTypeDomainMapper;
    }

    /**
     * Get PermissionCriterionResolver.
     *
     * @todo Move out from this & other repo instances when services becomes proper services in DIC terms using factory.
     *
     * @return \eZ\Publish\API\Repository\PermissionCriterionResolver
     */
    protected function getPermissionCriterionResolver()
    {
        return $this->getCachedPermissionsResolver();
    }

    /**
     * @return \eZ\Publish\API\Repository\PermissionCriterionResolver|\eZ\Publish\API\Repository\PermissionResolver
     */
    protected function getCachedPermissionsResolver()
    {
        if ($this->permissionsHandler === null) {
            $this->permissionsHandler = new CachedPermissionService(
                $permissionResolver = new Permission\PermissionResolver(
                    $this->getRoleDomainMapper(),
                    $this->getLimitationService(),
                    $this->persistenceHandler->userHandler(),
                    $this->currentUserRef
                ),
                new PermissionCriterionResolver(
                    $permissionResolver,
                    $this->getLimitationService()
                )
            );
        }

        return $this->permissionsHandler;
    }

    /**
     * Begin transaction.
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->persistenceHandler->beginTransaction();
    }

    /**
     * Commit transaction.
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function commit()
    {
        try {
            $this->persistenceHandler->commit();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Rollback transaction.
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        try {
            $this->persistenceHandler->rollback();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }
}
