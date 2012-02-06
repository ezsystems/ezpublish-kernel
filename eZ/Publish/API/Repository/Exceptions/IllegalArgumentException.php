<?php
namespace eZ\Publish\API\Repository\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ForbiddenException;

/**
 *
 * This exception is throen if a service method is called with an illegal or non appriprite value
 *
 */
abstract class IllegalArgumentException extends ForbiddenException
{
}
