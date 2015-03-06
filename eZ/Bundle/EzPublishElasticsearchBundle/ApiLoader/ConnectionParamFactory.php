<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 *
 */
class ConnectionParamFactory extends ContainerAware
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider
     */
    protected $repositoryProvider;

    /**
     *
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider $repositoryProvider
     */
    public function __construct( StorageRepositoryProvider $repositoryProvider )
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    /**
     *
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        $repositoryConfig = $this->repositoryProvider->getRepositoryConfig();
        $connectionName = $repositoryConfig["search"]["connection"];

        $parameterId = "ez_elasticsearch.connection.{$connectionName}.{$name}";

        if ( !$this->container->hasParameter( $parameterId ) )
        {
            throw new InvalidConfigurationException(
                "Unknown parameter with id '{$parameterId}'"
            );
        }

        return $this->container->getParameter( $parameterId );
    }
}
