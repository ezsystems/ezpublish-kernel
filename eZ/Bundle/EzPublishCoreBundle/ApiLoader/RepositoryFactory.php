<?php
/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler,
    eZ\Publish\SPI\IO\Handler as IoHandler,
    eZ\Publish\SPI\Limitation\Type as SPILimitationType,
    eZ\Publish\API\Repository\Repository,
    Symfony\Component\DependencyInjection\ContainerInterface,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class RepositoryFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Collection of fieldTypes, lazy loaded via a closure
     *
     * @var \Closure[]
     */
    protected $fieldTypes;

    /**
     * Collection of external storage handlers for field types that need them
     *
     * @var \Closure[]
     */
    protected $externalStorages = array();

    /**
     * Collection of limitation types for the RoleService.
     *
     * @var \eZ\Publish\SPI\Limitation\Type[]
     */
    protected $roleLimitations = array();

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * Builds the main repository, heart of eZ Publish API
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\IO\Handler $ioHandler
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository( PersistenceHandler $persistenceHandler, IoHandler $ioHandler )
    {
        $repositoryClass = $this->container->getParameter( 'ezpublish.api.repository.class' );
        return new $repositoryClass(
            $persistenceHandler,
            $ioHandler,
            array(
                'fieldType'     => $this->fieldTypes,
                'role'          => array(
                    'limitationTypes'   => $this->roleLimitations
                )
            )
        );
    }

    /**
     * Returns a closure which returns the repository.
     * To be used when lazy loading is needed.
     *
     * @return \Closure
     */
    public function buildLazyRepository()
    {
        $container = $this->container;
        return function () use ( $container )
        {
            static $repository;
            if ( !$repository instanceof Repository )
            {
                $repository = $container->get( 'ezpublish.api.repository' );
            }

            return $repository;
        };
    }

    /**
     * Registers an eZ Publish field type.
     * Field types are being registered as a closure so that they will be lazy loaded.
     *
     * @param string $fieldTypeServiceId The field type service Id
     * @param string $fieldTypeAlias The field type alias (e.g. "ezstring")
     */
    public function registerFieldType( $fieldTypeServiceId, $fieldTypeAlias )
    {
        $container = $this->container;
        $this->fieldTypes[$fieldTypeAlias] = function() use ( $container, $fieldTypeServiceId )
        {
            return $container->get( $fieldTypeServiceId );
        };
    }

    /**
     * Registers an external storage handler for a field type, identified by $fieldTypeAlias.
     * They are being registered as closures so that they will be lazy loaded.
     *
     * @param string $serviceId The external storage handler service Id
     * @param string $fieldTypeAlias The field type alias (e.g. "ezstring")
     */
    public function registerExternalStorageHandler( $serviceId, $fieldTypeAlias )
    {
        $container = $this->container;
        $this->externalStorages[$fieldTypeAlias] = function () use ( $container, $serviceId )
        {
            return $container->get( $serviceId );
        };
    }

    /**
     * Registers a limitation type for the RoleService.
     *
     * @param string $limitationName
     * @param \eZ\Publish\SPI\Limitation\Type $limitationType
     */
    public function registerLimitationType( $limitationName, SPILimitationType $limitationType )
    {
        $this->roleLimitations[$limitationName] = $limitationType;
    }

    /**
     * Returns registered external storage handlers for field types (as closures to be lazy loaded in the public API)
     *
     * @return \Closure[]
     */
    public function getExternalStorageHandlers()
    {
        return $this->externalStorages;
    }

    /**
     * Returns a service based on a name string (content => contentService, etc)
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param $serviceName
     * @return mixed
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the method/service is invalid
     */
    public function buildService( Repository $repository, $serviceName )
    {
        $methodName = 'get' . $serviceName . 'Service';
        if ( !method_exists( $repository, $methodName ) )
        {
            throw new InvalidArgumentException( $serviceName, "No such service" );
        }
        return $repository->$methodName();
    }
}
