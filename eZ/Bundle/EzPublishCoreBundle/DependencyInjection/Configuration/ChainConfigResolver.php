<?php
/**
 * File containing the ChainConfigResolver class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;

class ChainConfigResolver implements ConfigResolverInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface[]
     */
    protected $resolvers = array();

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface[]
     */
    protected $sortedResolvers;

    /**
     * Registers $mapper as a valid mapper to be used in the configuration mapping chain.
     * When this mapper will be called in the chain depends on $priority. The highest $priority is, the earliest the router will be called.
     *
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $resolver
     * @param int $priority
     */
    public function addResolver( ConfigResolverInterface $resolver, $priority = 0 )
    {
        $priority = (int)$priority;
        if ( !isset( $this->resolvers[$priority] ) )
            $this->resolvers[$priority] = array();

        $this->resolvers[$priority][] = $resolver;
        $this->sortedResolvers = array();
    }

    /**
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface[]
     */
    public function getAllResolvers()
    {
        if ( empty( $this->sortedResolvers ) )
            $this->sortedResolvers = $this->sortResolvers();

        return $this->sortedResolvers;
    }

    /**
     * Sort the registered mappers by priority.
     * The highest priority number is the highest priority (reverse sorting)
     *
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface[]
     */
    protected function sortResolvers()
    {
        $sortedResolvers = array();
        krsort( $this->resolvers );

        foreach ( $this->resolvers as $resolvers )
        {
            $sortedResolvers = array_merge( $sortedResolvers, $resolvers );
        }

        return $sortedResolvers;
    }

    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName The parameter name, without $prefix and the current scope (i.e. siteaccess name).
     * @param string $namespace Namespace for the parameter name. If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for.
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     *
     * @return mixed
     */
    public function getParameter( $paramName, $namespace = null, $scope = null )
    {
        foreach ( $this->getAllResolvers() as $resolver )
        {
            try
            {
                return $resolver->getParameter( $paramName, $namespace, $scope );
            }
            catch ( ParameterNotFoundException $e )
            {
                // Do nothing, just let the next resolver handle it
            }
        }

        // Finally throw a ParameterNotFoundException since the chain resolver couldn't find any valid resolver for demanded parameter
        throw new ParameterNotFoundException( $paramName, $namespace );
    }

    /**
     * Checks if $paramName exists in $namespace
     *
     * @param string $paramName
     * @param string $namespace If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for.
     *
     * @return boolean
     */
    public function hasParameter( $paramName, $namespace = null, $scope = null )
    {
        foreach ( $this->getAllResolvers() as $resolver )
        {
            $hasParameter = $resolver->hasParameter( $paramName, $namespace, $scope );
            if ( $hasParameter )
                return true;
        }

        return false;
    }

    /**
     * Changes the default namespace to look parameter into.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultNamespace( $defaultNamespace )
    {
        foreach ( $this->getAllResolvers() as $resolver )
        {
            $resolver->setDefaultNamespace( $defaultNamespace );
        }
    }

    /**
     * Not supported
     *
     * @throws \LogicException
     */
    public function getDefaultNamespace()
    {
        throw new \LogicException( 'getDefaultNamespace() is not supported by the ChainConfigResolver' );
    }
}
