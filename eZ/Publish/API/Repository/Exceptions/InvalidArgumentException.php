<?php
namespace eZ\Publish\API\Repository\Exceptions;

use InvalidArgumentException as PHPInvalidArgumentException;

/**
 *
 * This exception is throen if a service method is called with an illegal or non appriprite value
 *
 */
abstract class InvalidArgumentException extends PHPInvalidArgumentException
{
}
