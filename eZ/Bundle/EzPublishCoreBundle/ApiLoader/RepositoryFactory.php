<?php

/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeNameableCollectionFactory;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
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
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeNameableCollectionFactory
     */
    protected $fieldTypeNameableCollectionFactory;

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
     * @var array
     */
    private $policyMap;

    public function __construct(
        ConfigResolverInterface $configResolver,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        FieldTypeNameableCollectionFactory $fieldTypeNameableCollectionFactory,
        $repositoryClass,
        array $policyMap
    ) {
        $this->configResolver = $configResolver;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
        $this->fieldTypeNameableCollectionFactory = $fieldTypeNameableCollectionFactory;
        $this->repositoryClass = $repositoryClass;
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
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository(PersistenceHandler $persistenceHandler, SearchHandler $searchHandler)
    {
        $repository = new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            array(
                'fieldType' => $this->fieldTypeCollectionFactory->getFieldTypes(),
                'nameableFieldTypes' => $this->fieldTypeNameableCollectionFactory->getNameableFieldTypes(),
                'role' => array(
                    'limitationTypes' => $this->roleLimitations,
                    'policyMap' => $this->policyMap,
                ),
                'languages' => $this->configResolver->getParameter('languages'),
            ),
            new UserReference($this->configResolver->getParameter('anonymous_user_id'))
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
