<?php
namespace eZ\Publish\API\Repository\Exceptions;

use RuntimeException;

/**
 * This Exception is thrown if the user has is not allowed to perform a service operation
 */
abstract class UnauthorizedException extends RuntimeException
{
}

