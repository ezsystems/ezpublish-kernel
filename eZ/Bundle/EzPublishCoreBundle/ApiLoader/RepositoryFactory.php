<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\PermissionService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Permission\LimitationService;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperFactoryInterface;
use eZ\Publish\Core\Repository\User\PasswordHashServiceInterface;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Mapper;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use eZ\Publish\SPI\Repository\Validator\ContentValidator;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class RepositoryFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var string */
    private $repositoryClass;

    /**
     * Map of system configured policies.
     *
     * @var array
     */
    private $policyMap;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    private $languageResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        $repositoryClass,
        array $policyMap,
        LanguageResolver $languageResolver,
        LoggerInterface $logger = null
    ) {
        $this->configResolver = $configResolver;
        $this->repositoryClass = $repositoryClass;
        $this->policyMap = $policyMap;
        $this->languageResolver = $languageResolver;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * Builds the main repository, heart of eZ Publish API.
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Event / Cache / * functionality.
     */
    public function buildRepository(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor,
        FieldTypeRegistry $fieldTypeRegistry,
        PasswordHashServiceInterface $passwordHashService,
        ThumbnailStrategy $thumbnailStrategy,
        ProxyDomainMapperFactoryInterface $proxyDomainMapperFactory,
        Mapper\ContentDomainMapper $contentDomainMapper,
        Mapper\ContentTypeDomainMapper $contentTypeDomainMapper,
        Mapper\RoleDomainMapper $roleDomainMapper,
        Mapper\ContentMapper $contentMapper,
        ContentValidator $contentValidator,
        LimitationService $limitationService,
        PermissionService $permissionService
    ): Repository {
        $config = $this->container->get('ezpublish.api.repository_configuration_provider')->getRepositoryConfig();

        return new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            $backgroundIndexer,
            $relationProcessor,
            $fieldTypeRegistry,
            $passwordHashService,
            $thumbnailStrategy,
            $proxyDomainMapperFactory,
            $contentDomainMapper,
            $contentTypeDomainMapper,
            $roleDomainMapper,
            $contentMapper,
            $contentValidator,
            $limitationService,
            $this->languageResolver,
            $permissionService,
            [
                'role' => [
                    'policyMap' => $this->policyMap,
                ],
                'languages' => $this->configResolver->getParameter('languages'),
                'content' => ['default_version_archive_limit' => $config['options']['default_version_archive_limit']],
            ],
            $this->logger
        );
    }
}
