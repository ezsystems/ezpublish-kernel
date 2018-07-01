<?php

/**
 * File containing the OutputGeneratorException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output\Exceptions;

use RuntimeException;

/**
 * Invalid output generation.
 */
class OutputGeneratorException extends RuntimeException
{
    /**
     * Construct from error message.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct(
            'Output visiting failed: ' . $message
        );
    }
}
