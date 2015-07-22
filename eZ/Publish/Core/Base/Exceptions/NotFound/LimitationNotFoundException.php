<?php

/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Base\Exceptions\NotFound;

use eZ\Publish\Core\Base\Exceptions\Httpable;
use Exception;
use RuntimeException;

/**
 * Limitation Not Found Exception implementation.
 */
class LimitationNotFoundException extends RuntimeException implements Httpable
{
    /**
     * Creates a Limitation Not Found exception with info on how to fix.
     *
     * @param string $limitation
     * @param \Exception|null $previous
     */
    public function __construct($limitation, Exception $previous = null)
    {
        parent::__construct(
            "Limitation '{$limitation}' not found, needs to be implemented or configured to use "
            . 'Limitation\\BlockingLimitationType (%ezpublish.api.role.limitation_type.blocking.class%)',
            self::INTERNAL_ERROR,
            $previous
        );
    }
}
