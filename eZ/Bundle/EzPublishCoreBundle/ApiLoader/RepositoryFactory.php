<?php

/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class RepositoryFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory
     */
    protected $fieldTypeCollectionFactory;

    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * Collection of limitation types for the RoleService.
     *
     * @var \eZ\Publish\SPI\Limitation\Type[]
     */
    protected $roleLimitations = array();

    /**
     * Map of system configured policies.
     *
     * @var array
     */
    private $policyMap;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigResolverInterface $configResolver,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        $repositoryClass,
        array $policyMap,
        LoggerInterface $logger = null
    ) {
        $this->configResolver = $configResolver;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
        $this->repositoryClass = $repositoryClass;
        $this->policyMap = $policyMap;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * Builds the main repository, heart of eZ Publish API.
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Signal / Cache / * functionality.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param \eZ\Publish\Core\Search\Common\BackgroundIndexer $backgroundIndexer
     * @param \eZ\Publish\Core\Repository\Helper\RelationProcessor $relationProcessor
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor
    ) {
        $config = $this->container->get('ezpublish.api.repository_configuration_provider')->getRepositoryConfig();

        $repository = new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            $backgroundIndexer,
            $relationProcessor,
            array(
                'fieldType' => $this->fieldTypeCollectionFactory->getFieldTypes(),
                'role' => array(
                    'limitationTypes' => $this->roleLimitations,
                    'policyMap' => $this->policyMap,
                ),
                'languages' => $this->configResolver->getParameter('languages'),
                'content' => ['default_version_archive_limit' => $config['options']['default_version_archive_limit']],
            ),
            new UserReference($this->configResolver->getParameter('anonymous_user_id')),
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
