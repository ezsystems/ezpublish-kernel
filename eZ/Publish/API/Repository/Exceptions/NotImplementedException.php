<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\NotImplementedException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown if a feature has not been implemented
 * _intentionally_. The main purpose is the search handler, where some features
 * are just not supported in the legacy search implementation.
 */
class NotImplementedException extends ForbiddenException
{
    /**
     * Generates: Intentionally not implemented: {$feature}
     *
     * @param string $feature
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct( $feature, $code = 0, \Exception $previous = null )
    {
        parent::__construct( "Intentionally not implemented: {$feature}", $code, $previous );
    }
}
