<?php

/**
 * File containing the InvalidTypeException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output\Exceptions;

use RuntimeException;

/**
 * Output visiting invalid type exception.
 */
class InvalidTypeException extends RuntimeException
{
    /**
     * Construct from invalid data.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        parent::__construct(
            'You must provide a ValueObject for visiting, "' . gettype($data) . '" provided.'
        );
    }
}
