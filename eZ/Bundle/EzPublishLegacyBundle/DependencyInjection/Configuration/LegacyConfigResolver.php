<?php
/**
 * File containing the LegacyConfigResolver class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZINI;

/**
 * Configuration resolver for eZ Publish legacy.
 * Will help you get settings from the legacy kernel (old ini files).
 *
 * <code>
 * // From a controller
 * $legacyResolver = $this->container->get( 'ezpublish_legacy.config.resolver' );
 * // Get [DebugSettings].DebugOutput from site.ini
 * $debugOutput = $legacyResolver->getParameter( 'DebugSettings.DebugOutput' );
 * // Get [ImageMagick].ExecutablePath from image.ini
 * $imageMagickPath = $legacyResolver->getParameter( 'ImageMagick.ExecutablePath', 'image' );
 * // Get [DatabaseSettings].Database from site.ini, for ezdemo_site_admin siteaccess
 * $databaseName = $legacyResolver->getParameter( 'DatabaseSettings.Database', 'site', 'ezdemo_site_admin' );
 *
 * // Note that the examples above are also applicable for hasParameter().
 * </code>
 */
class LegacyConfigResolver implements ConfigResolverInterface
{
    /**
     * @var \Closure
     */
    protected $legacyKernelClosure;

    /**
     * @var string
     */
    protected $defaultNamespace;

    public function __construct( \Closure $legacyKernelClosure, $defaultNamespace )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $kernelClosure = $this->legacyKernelClosure;
        return $kernelClosure();
    }

    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName String containing dot separated INI group name and param name.
     *                          Must follow the following format: <iniGroupName>.<paramName>
     * @param string $namespace The legacy INI file name, without the suffix (i.e. without ".ini").
     * @param string $scope A specific siteaccess to look into. Defaults to the current siteaccess.
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     *
     * @return mixed
     */
    public function getParameter( $paramName, $namespace = null, $scope = null )
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace( '.ini', '', $namespace );
        list( $iniGroup, $paramName ) = explode( '.', $paramName, 2 );

        return $this->getLegacyKernel()->runCallback(
            function () use ( $iniGroup, $paramName, $namespace, $scope )
            {
                if ( isset( $scope ) )
                {
                    $ini = eZINI::getSiteAccessIni( $scope, "$namespace.ini" );
                }
                else
                {
                    $ini = eZINI::instance( "$namespace.ini" );
                }

                if ( !$ini->hasVariable( $iniGroup, $paramName ) )
                    throw new ParameterNotFoundException( $paramName, "$namespace.ini" );

                return $ini->variable( $iniGroup, $paramName );
            },
            false
        );
    }

    /**
     * Returns values for $groupName, in $namespace.
     *
     * @param string $groupName String containing an INI group name.
     * @param string $namespace The legacy INI file name, without the suffix (i.e. without ".ini").
     * @param string $scope A specific siteaccess to look into. Defaults to the current siteaccess.
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     *
     * @todo Implement in ConfigResolver interface
     *
     * @return array
     */
    public function getGroup( $groupName, $namespace = null, $scope = null )
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace( '.ini', '', $namespace );

        return $this->getLegacyKernel()->runCallback(
            function () use ( $groupName, $namespace, $scope )
            {
                if ( isset( $scope ) )
                {
                    $ini = eZINI::getSiteAccessIni( $scope, "$namespace.ini" );
                }
                else
                {
                    $ini = eZINI::instance( "$namespace.ini" );
                }

                if ( !$ini->hasGroup( $groupName ) )
                    throw new ParameterNotFoundException( $groupName, "$namespace.ini" );

                return $ini->group( $groupName );
            },
            false
        );
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
        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace( '.ini', '', $namespace );
        list( $iniGroup, $paramName ) = explode( '.', $paramName, 2 );

        return $this->getLegacyKernel()->runCallback(
            function () use ( $iniGroup, $paramName, $namespace, $scope )
            {
                if ( isset( $scope ) )
                {
                    $ini = eZINI::getSiteAccessIni( $scope, "$namespace.ini" );
                }
                else
                {
                    $ini = eZINI::instance( "$namespace.ini" );
                }

                return $ini->hasVariable( $iniGroup, $paramName );
            },
            false
        );
    }

    /**
     * Changes the default namespace to look parameter into.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultNamespace( $defaultNamespace )
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * Returns the current default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }
}
