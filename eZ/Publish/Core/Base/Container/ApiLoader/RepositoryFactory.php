<?php
/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\ApiLoader;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerAware;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class RepositoryFactory extends ContainerAware
{
    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * Collection of fieldTypes, lazy loaded via a closure
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

    public function __construct( $repositoryClass, FieldTypeCollectionFactory $fieldTypeCollectionFactory )
    {
        $this->repositoryClass = $repositoryClass;
        $this->fieldTypeCollectionFactory = $fieldTypeCollectionFactory;
    }

    /**
     * Builds the main repository, heart of eZ Publish API
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Signal / Cache / * functionality.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function buildRepository( PersistenceHandler $persistenceHandler )
    {
        $repository = new $this->repositoryClass(
            $persistenceHandler,
            array(
                'fieldType'     => $this->fieldTypeCollectionFactory->getFieldTypes(),
                'role'          => array(
                    'limitationTypes'   => $this->roleLimitations
                ),
                'languages'     => $this->container->getParameter( "languages" )
            )
        );

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $anonymousUser = $repository->getUserService()->loadUser(
            $this->container->getParameter( "anonymous_user_id" )
        );
        $repository->setCurrentUser( $anonymousUser );

        return $repository;
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
     * Returns a service based on a name string (content => contentService, etc)
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param string $serviceName
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return mixed
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
