<?php

/**
 * File containing the ServerException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\Core\REST\Client\Values\ErrorMessage;
use Exception;

/**
 * Wraps a eZ\Publish\Core\REST\Client\Values\ErrorMessage to provide info from server.
 *
 * To be used as previous exception when throwing client exceptions for server error response codes.
 */
class ServerException extends Exception
{
    protected $trace;

    /**
     * @param ErrorMessage $error
     */
    public function __construct(ErrorMessage $error)
    {
        // Use description, this is the exception message from sever, message is only the HTTP status message.
        parent::__construct($error->description, $error->code);

        // These are only set if server is running in debug mode, but even if not set we want to overwrite the values.
        $this->file = (string)$error->file;
        $this->line = (int)$error->line;
        $this->trace = explode("\n", (string)$error->trace);
    }
}
