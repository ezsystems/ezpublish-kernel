<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * ConnectionParameterFactory will return connection parameters resolved for a current siteaccess.
 */
class ConnectionParameterFactory extends ContainerAware
{
    /**
     * Holds RepositoryConfigurationProvider, used to get repository configuration
     * resolved for current siteaccess.
     *
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    protected $repositoryProvider;

    /**
     * Alias of the bundle's extension, used as a root key for semantic configuration
     *
     * @var string
     */
    protected $extensionAlias;

    /**
     * Name of the key used for the name of the default connection
     *
     * @var string
     */
    protected $defaultConnectionKey;

    /**
     * Prefix of the default parameters, defined in Core/settings
     *
     * @var string
     */
    protected $defaultParameterPrefix;

    /**
     * Construct from RepositoryConfigurationProvider and container parameters
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider $repositoryProvider
     * @param string $extensionAlias
     * @param string $defaultConnectionKey
     * @param string $defaultParameterPrefix
     */
    public function __construct(
        RepositoryConfigurationProvider $repositoryProvider,
        $extensionAlias,
        $defaultConnectionKey,
        $defaultParameterPrefix
    )
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->extensionAlias = $extensionAlias;
        $this->defaultConnectionKey = $defaultConnectionKey;
        $this->defaultParameterPrefix = $defaultParameterPrefix;
    }

    /**
     * Returns search engine connection parameter with given $name,
     * resolved for a current siteaccess.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        $defaultConnectionId = "{$this->extensionAlias}.{$this->defaultConnectionKey}";
        $repositoryConfig = $this->repositoryProvider->getRepositoryConfig();
        $repositoryAlias = $repositoryConfig["alias"];
        $connectionName = $repositoryConfig["search"]["connection"];

        if ( $connectionName === null )
        {
            if ( !$this->container->hasParameter( $defaultConnectionId ) )
            {
                $exception = new InvalidConfigurationException(
                    "Default Solr Search Engine connection is not defined."
                );

                $exception->setPath( "ezpublish.repositories.{$repositoryAlias}.search" );
                $exception->addHint(
                    "You can define it under '{$this->extensionAlias}' extension, using " .
                    "'{$this->defaultConnectionKey}' key. Alternatively, explicitly configure " .
                    "search engine with existing connection name."
                );

                throw $exception;
            }

            $connectionName = $this->container->getParameter( $defaultConnectionId );
        }

        if ( $repositoryConfig["search"]["engine"] === "solr" )
        {
            $parameterPrefix = "{$this->extensionAlias}.connection.{$connectionName}";
        }
        else
        {
            // When this engine is not configured for an access, use parameters
            // defined in Core/settings
            $parameterPrefix = $this->defaultParameterPrefix;
        }

        $parameterId = "{$parameterPrefix}.{$name}";

        if ( !$this->container->hasParameter( $parameterId ) )
        {
            throw new InvalidConfigurationException(
                "Unknown parameter with id '{$parameterId}'"
            );
        }

        return $this->container->getParameter( $parameterId );
    }
}
