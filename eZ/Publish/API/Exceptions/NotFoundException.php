<?php
namespace eZ\Publish\API\Exceptions;

/**
 * This Exception is thrown if an object referencenced by an id or identifier
 * could not be found in the repository.
 * @package eZ\Publish\API\Exceptions
 *
 */
abstract class NotFoundException extends RuntimeException
{
}

