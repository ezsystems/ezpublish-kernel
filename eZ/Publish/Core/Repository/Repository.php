<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\Repository\User\PasswordHashServiceInterface;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Permission\CachedPermissionService;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;

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

    /** @var \eZ\Publish\Core\FieldType\FieldTypeRegistry */
    private $fieldTypeRegistry;

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
     * Instance of URL service.
     *
     * @var \eZ\Publish\Core\Repository\URLService
     */
    protected $urlService;

    /**
     * Instance of Bookmark service.
     *
     * @var \eZ\Publish\API\Repository\BookmarkService
     */
    protected $bookmarkService;

    /**
     * Instance of Notification service.
     *
     * @var \eZ\Publish\API\Repository\NotificationService
     */
    protected $notificationService;

    /**
     * Instance of User Preference service.
     *
     * @var \eZ\Publish\API\Repository\UserPreferenceService
     */
    protected $userPreferenceService;

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

    /** @var \eZ\Publish\Core\Repository\Helper\RoleDomainMapper */
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

    /** @var \eZ\Publish\Core\Search\Common\BackgroundIndexer|null */
    protected $backgroundIndexer;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\Core\Repository\User\PasswordHashServiceInterface */
    private $passwordHashService;

    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy */
    private $thumbnailStrategy;

    /**
     * Construct repository object with provided storage engine.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param \eZ\Publish\Core\Search\Common\BackgroundIndexer $backgroundIndexer
     * @param \eZ\Publish\Core\Repository\Helper\RelationProcessor $relationProcessor
     * @param \eZ\Publish\Core\FieldType\FieldTypeRegistry $fieldTypeRegistry
     * @param \eZ\Publish\Core\Repository\User\PasswordHashServiceInterface $passwordHashGenerator
     * @param \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy $thumbnailStrategy
     * @param array $serviceSettings
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor,
        FieldTypeRegistry $fieldTypeRegistry,
        PasswordHashServiceInterface $passwordHashGenerator,
        ThumbnailStrategy $thumbnailStrategy,
        array $serviceSettings = [],
        LoggerInterface $logger = null
    ) {
        $this->persistenceHandler = $persistenceHandler;
        $this->searchHandler = $searchHandler;
        $this->backgroundIndexer = $backgroundIndexer;
        $this->relationProcessor = $relationProcessor;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->passwordHashService = $passwordHashGenerator;
        $this->thumbnailStrategy = $thumbnailStrategy;
        $this->serviceSettings = $serviceSettings + [
            'content' => [],
            'contentType' => [],
            'location' => [],
            'section' => [],
            'role' => [],
            'user' => [
                'anonymousUserID' => 10,
            ],
            'language' => [],
            'trash' => [],
            'io' => [],
            'objectState' => [],
            'search' => [],
            'urlAlias' => [],
            'urlWildcard' => [],
            'nameSchema' => [],
            'languages' => [],
        ];

        if (!empty($this->serviceSettings['languages'])) {
            $this->serviceSettings['language']['languages'] = $this->serviceSettings['languages'];
        }

        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function sudo(callable $callback, RepositoryInterface $outerRepository = null)
    {
        return $this->getPermissionResolver()->sudo($callback, $outerRepository ?? $this);
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
            $this->fieldTypeRegistry,
            $this->getPermissionResolver(),
            $this->serviceSettings['content'],
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
            $this->getPermissionResolver(),
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
            $this->persistenceHandler->userHandler(),
            $this->getDomainMapper(),
            $this->getContentTypeDomainMapper(),
            $this->fieldTypeRegistry,
            $this->getPermissionResolver(),
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
            $this->getPermissionResolver(),
            $this->serviceSettings['location'],
            $this->logger
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
            $this->getPermissionCriterionResolver(),
            $this->getPermissionResolver(),
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
            $this->persistenceHandler->locationHandler(),
            $this->getPermissionCriterionResolver(),
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
            $this->getPermissionResolver(),
            $this->persistenceHandler->userHandler(),
            $this->persistenceHandler->locationHandler(),
            $this->passwordHashService,
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
            $this->getNameSchemaService(),
            $this->getPermissionResolver(),
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
            $this->getPermissionResolver(),
            $this->serviceSettings['urlWildcard']
        );

        return $this->urlWildcardService;
    }

    /**
     * Get URLService.
     *
     * @return \eZ\Publish\API\Repository\URLService
     */
    public function getURLService()
    {
        if ($this->urlService !== null) {
            return $this->urlService;
        }

        $this->urlService = new URLService(
            $this,
            $this->persistenceHandler->urlHandler(),
            $this->getPermissionResolver()
        );

        return $this->urlService;
    }

    /**
     * Get BookmarkService.
     *
     * @return \eZ\Publish\API\Repository\BookmarkService
     */
    public function getBookmarkService()
    {
        if ($this->bookmarkService === null) {
            $this->bookmarkService = new BookmarkService(
                $this,
                $this->persistenceHandler->bookmarkHandler()
            );
        }

        return $this->bookmarkService;
    }

    /**
     * Get UserPreferenceService.
     *
     * @return \eZ\Publish\API\Repository\UserPreferenceService
     */
    public function getUserPreferenceService()
    {
        if ($this->userPreferenceService === null) {
            $this->userPreferenceService = new UserPreferenceService(
                $this,
                $this->persistenceHandler->userPreferenceHandler()
            );
        }

        return $this->userPreferenceService;
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
            $this->getPermissionResolver(),
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

        $this->fieldTypeService = new FieldTypeService($this->fieldTypeRegistry);

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
            $this->fieldTypeRegistry,
            $this->serviceSettings['nameSchema']
        );

        return $this->nameSchemaService;
    }

    /**
     * @return \eZ\Publish\API\Repository\NotificationService
     */
    public function getNotificationService(): NotificationServiceInterface
    {
        if (null !== $this->notificationService) {
            return $this->notificationService;
        }

        $this->notificationService = new NotificationService(
            $this->persistenceHandler->notificationHandler(),
            $this->getPermissionResolver()
        );

        return $this->notificationService;
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
            $this->getContentTypeDomainMapper(),
            $this->persistenceHandler->contentLanguageHandler(),
            $this->fieldTypeRegistry,
            $this->thumbnailStrategy
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
            $this->persistenceHandler->contentTypeHandler(),
            $this->persistenceHandler->contentLanguageHandler(),
            $this->fieldTypeRegistry
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
                    new UserReference($this->serviceSettings['user']['anonymousUserID']),
                    $this->serviceSettings['role']['policyMap']
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
