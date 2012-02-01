<?php
namespace ezp\PublicAPI\Exceptions;

/**
 * This Exception is thrown if an object referencenced by an id or identifier
 * could not be found in the repository.
 * @package ezp\PublicAPI\Exceptions
 *
 */
abstract class NotFoundException extends RuntimeException
{
}

