<?php
/**
 * File containing the RepositoryFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Limitation\Type as SPILimitationType;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerAware;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class RepositoryFactory extends ContainerAware
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var string
     */
    private $repositoryClass;

    /**
     * Collection of fieldTypes, lazy loaded via a closure
     *
     * @var \Closure[]
     */
    protected $fieldTypes;

    /**
     * Collection of limitation types for the RoleService.
     *
     * @var \eZ\Publish\SPI\Limitation\Type[]
     */
    protected $roleLimitations = array();

    public function __construct( ConfigResolverInterface $configResolver, $repositoryClass )
    {
        $this->configResolver = $configResolver;
        $this->repositoryClass = $repositoryClass;
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
                'fieldType'     => $this->fieldTypes,
                'role'          => array(
                    'limitationTypes'   => $this->roleLimitations
                ),
                'languages'     => $this->configResolver->getParameter( 'languages' )
            )
        );

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $anonymousUser = $repository->getUserService()->loadUser(
            $this->configResolver->getParameter( "anonymous_user_id" )
        );
        $repository->setCurrentUser( $anonymousUser );

        return $repository;
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
        $this->fieldTypes[$fieldTypeAlias] = function () use ( $container, $fieldTypeServiceId )
        {
            return $container->get( $fieldTypeServiceId );
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
     * Returns registered field types (as closures to be lazy loaded in the public API)
     *
     * @return \Closure[]
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
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
