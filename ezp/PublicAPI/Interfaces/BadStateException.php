<?php
namespace ezp\PublicAPI\Interfaces;
/**
 *
 * This Exception is thrown if a method is called with an value referencing an object which is not in the right state
 *
 * @package ezp\PublicAPI\Interfaces
 */
abstract class BadStateException extends ForbiddenException
{
}
