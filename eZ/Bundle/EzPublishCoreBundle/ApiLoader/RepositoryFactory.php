<?php

/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperFactory;
use eZ\Publish\Core\Repository\User\PasswordHashServiceInterface;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
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
     * Collection of limitation types for the RoleService.
     *
     * @var \eZ\Publish\SPI\Limitation\Type[]
     */
    protected $roleLimitations = [];

    /**
     * Map of system configured policies.
     *
     * @var array
     */
    private $policyMap;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        ConfigResolverInterface $configResolver,
        $repositoryClass,
        array $policyMap,
        LoggerInterface $logger = null
    ) {
        $this->configResolver = $configResolver;
        $this->repositoryClass = $repositoryClass;
        $this->policyMap = $policyMap;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * Builds the main repository, heart of eZ Publish API.
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Event / Cache / * functionality.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param \eZ\Publish\Core\Search\Common\BackgroundIndexer $backgroundIndexer
     * @param \eZ\Publish\Core\Repository\Helper\RelationProcessor $relationProcessor
     * @param \eZ\Publish\Core\FieldType\FieldTypeRegistry $fieldTypeRegistry
     * @param \eZ\Publish\Core\Repository\User\PasswordHashServiceInterface $passwordHashService
     * @param \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperFactory $proxyDomainMapperFactory
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor,
        FieldTypeRegistry $fieldTypeRegistry,
        PasswordHashServiceInterface $passwordHashService,
        ProxyDomainMapperFactory $proxyDomainMapperFactory
    ) {
        $config = $this->container->get('ezpublish.api.repository_configuration_provider')->getRepositoryConfig();

        $repository = new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            $backgroundIndexer,
            $relationProcessor,
            $fieldTypeRegistry,
            $passwordHashService,
            $proxyDomainMapperFactory,
            [
                'role' => [
                    'limitationTypes' => $this->roleLimitations,
                    'policyMap' => $this->policyMap,
                ],
                'languages' => $this->configResolver->getParameter('languages'),
                'content' => ['default_version_archive_limit' => $config['options']['default_version_archive_limit']],
            ],
            $this->logger
        );

        return $repository;
    }

    /**
     * Registers a limitation type for the RoleService.
     *
     * @param string $limitationName
     * @param \eZ\Publish\SPI\Limitation\Type $limitationType
     */
    public function registerLimitationType($limitationName, SPILimitationType $limitationType)
    {
        $this->roleLimitations[$limitationName] = $limitationType;
    }
}
