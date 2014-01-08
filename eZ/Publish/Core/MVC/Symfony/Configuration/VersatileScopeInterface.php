<?php
/**
 * File containing the VersatileScopeInterface class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Configuration;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Allows a ConfigResolver to dynamically change their default scope.
 */
interface VersatileScopeInterface extends ConfigResolverInterface
{
    /**
     * Returns current default scope.
     *
     * @return string
     */
    public function getDefaultScope();

    /**
     * Sets a new default scope.
     *
     * @param string $scope
     */
    public function setDefaultScope( $scope );
}
