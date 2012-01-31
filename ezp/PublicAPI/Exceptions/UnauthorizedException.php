<?php
namespace ezp\PublicAPI\Exceptions;
/**
 * This Exception is thrown if theuser has is not allowed to perform a service operation
 */
abstract class UnauthorizedException extends ForbiddenException
{
}

