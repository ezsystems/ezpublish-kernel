<?php
/**
 * File containing the ConfigResolverInterface interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC;

/**
 * Interface for config resolvers.
 */
interface ConfigResolverInterface
{
    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName The parameter name, without $prefix and the current scope (i.e. siteaccess name).
     * @param string $namespace Namespace for the parameter name. If null, the default namespace will be used.
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     * @return mixed
     */
    public function getParameter( $paramName, $namespace = null );
}
