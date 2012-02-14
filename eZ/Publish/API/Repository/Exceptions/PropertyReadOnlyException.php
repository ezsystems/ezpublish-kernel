<?php
namespace eZ\Publish\API\Repository\Exceptions;

use RuntimeException;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 * 
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
abstract class PropertyReadOnlyException extends RuntimeException
{
}


