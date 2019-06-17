<?php

/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader;

use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class RepositoryFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory
     */
    protected $fieldTypeCollectionFactory;

    /**
     * Collection of limitation types for the RoleService.
     *
     * @var \eZ\Publish\SPI\Limitation\Type[]
     */
    protected $roleLimitations = array();

    /**
     * Policies map.
     *
     * @var array
     */
    protected $policyMap = array();

    public function __construct(
        $repositoryClass,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        array $policyMap
    ) {
        $this->repositoryClass = $repositoryClass;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
        $this->policyMap = $policyMap;
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
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor
    ) {
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
                'languages' => $this->container->getParameter('languages'),
            ),
            new UserReference($this->container->getParameter('anonymous_user_id'))
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

    /**
     * Returns a service based on a name string (content => contentService, etc).
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param string $serviceName
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return mixed
     */
    public function buildService(Repository $repository, $serviceName)
    {
        $methodName = 'get' . $serviceName . 'Service';
        if (!method_exists($repository, $methodName)) {
            throw new InvalidArgumentException($serviceName, 'No such service');
        }

        return $repository->$methodName();
    }
}
