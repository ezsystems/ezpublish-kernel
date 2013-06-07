<?php
/**
 * File containing the MatcherFactory interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher;

use eZ\Publish\API\Repository\Values\ValueObject;

interface MatcherFactoryInterface
{
    /**
     * Checks if $valueObject has a usable configuration for $viewType.
     * If so, the configuration hash will be returned.
     *
     * $valueObject can be for example a Location or a Content object.
     *
     * @param string $viewType
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     *
     * @return array|null The matched configuration as a hash, containing template or controller to use, or null if not matched.
     */
    public function match( $viewType, ValueObject $valueObject );
}
