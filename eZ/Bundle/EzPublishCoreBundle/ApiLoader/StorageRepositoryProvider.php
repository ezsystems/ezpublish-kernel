<?php
/**
 * File containing the StorageEngineFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * The storage engine factory.
 */
class StorageRepositoryProvider
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var array
     */
    private $repositories;

    /**
     * Hash of registered storage engines.
     * Key is the storage engine identifier, value persistence handler itself.
     *
     * @var \eZ\Publish\SPI\Persistence\Handler[]
     */
    protected $storageEngines = array();

    public function __construct( ConfigResolverInterface $configResolver, array $repositories )
    {
        $this->configResolver = $configResolver;
        $this->repositories = $repositories;
    }

    /**
     * @return array
     *
     * @throws \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function getRepositoryConfig()
    {
        // Takes configured repository as the reference, if it exists.
        // If not, the first configured repository is considered instead.
        $repositoryAlias = $this->configResolver->getParameter( 'repository' );
        if ( $repositoryAlias === null )
        {
            $aliases = array_keys( $this->repositories );
            $repositoryAlias = array_shift( $aliases );
        }

        if ( empty( $repositoryAlias ) || !isset( $this->repositories[$repositoryAlias] ) )
        {
            throw new InvalidRepositoryException(
                "Undefined repository '$repositoryAlias'. Did you forget to configure it in ezpublish_*.yml?"
            );
        }

        return array( 'alias' => $repositoryAlias ) + $this->repositories[$repositoryAlias];
    }
}
