<?php
namespace eZ\Publish\API\Repository\Exceptions;

use RuntimeException;

/**
 * This Exception is thrown if an accessed property in a value object was not found
 * 
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
abstract class PropertyNotFoundException extends RuntimeException
{
}


