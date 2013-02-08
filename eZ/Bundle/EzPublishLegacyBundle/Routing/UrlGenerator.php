<?php
/**
 * File containing the UrlGenerator class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Routing;

use eZModule;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator;
use Symfony\Component\Routing\RequestContext;

class UrlGenerator extends Generator
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    public function __construct( \Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    public function getLegacyKernel()
    {
        $kernelClosure = $this->legacyKernelClosure;
        return $kernelClosure();
    }

    /**
     * Generate the URL of an eZ Publish legacy module.
     * Existence of the module/view will be checked and an \InvalidArgumentException will be thrown if one or the other don't exist.
     *
     * @param string $legacyModuleUri The legacy module URI, including ordered params (e.g. "/content/view/full/2"
     * @param array $parameters Named parameters for the module/view
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function doGenerate( $legacyModuleUri, array $parameters )
    {
        // Removing leading and trailing slashes
        if ( strpos( $legacyModuleUri, '/' ) === 0 )
            $legacyModuleUri = substr( $legacyModuleUri, 1 );
        if ( strrpos( $legacyModuleUri, '/' ) === ( strlen( $legacyModuleUri ) - 1 ) )
            $legacyModuleUri = substr( $legacyModuleUri, 0, -1 );

        list( $moduleName, $viewName ) = explode( '/', $legacyModuleUri );

        return $this->getLegacyKernel()->runCallback(
            function () use ( $legacyModuleUri, $moduleName, $viewName, $parameters )
            {
                $module = eZModule::findModule( $moduleName );
                if ( !$module instanceof eZModule )
                    throw new \InvalidArgumentException( "Legacy module '$moduleName' doesn't exist. Cannot generate URL." );

                $moduleViews = $module->attribute( 'views' );
                if ( !isset( $moduleViews[$viewName] ) )
                    throw new \InvalidArgumentException( "Legacy module '$moduleName' doesn't have any view named '$viewName'. Cannot generate URL." );

                $unorderedParams = '';
                foreach ( $parameters as $paramName => $paramValue )
                {
                    if ( !is_scalar( $paramValue ) )
                        continue;

                    $unorderedParams .= "/($paramName)/$paramValue";
                }

                return "/$legacyModuleUri$unorderedParams";
            },
            false
        );
    }
}
