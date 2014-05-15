<?php
/**
 * Contains Not Found Exception implementation
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Exception;

/**
 * Not Found Exception implementation
 *
 * Use:
 *   throw new NotFound( 'Content', 42 );
 */
class NotFoundException extends APINotFoundException implements Httpable
{
    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $what, $identifier, Exception $previous = null )
    {
        parent::__construct(
            "Could not find '{$what}' with identifier '" .
            ( is_array( $identifier ) ? var_export( $identifier, true ) : $identifier ) .
            "'",
            self::NOT_FOUND,
            $previous
        );
    }
}
