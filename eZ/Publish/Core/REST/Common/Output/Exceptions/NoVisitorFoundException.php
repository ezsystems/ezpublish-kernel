<?php

/**
 * File containing the NoVisitorFoundException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output\Exceptions;

use RuntimeException;

/**
 * No output visitor found exception.
 */
class NoVisitorFoundException extends RuntimeException
{
    /**
     * Construct from tested classes.
     *
     * @param array $classes
     */
    public function __construct(array $classes)
    {
        parent::__construct(
            sprintf(
                'No visitor found for %s!',
                implode(', ', $classes)
            )
        );
    }
}
