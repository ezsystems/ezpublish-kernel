<?php
namespace eZ\Publish\API\Repository\Exceptions;

use RuntimeException;

/**
 * This Exception is thrown if an object referencenced by an id or identifier
 * could not be found in the repository.
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
abstract class NotFoundException extends RuntimeException
{
}

