<?php
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * An Exception which is thrown if an operation cannot be performed by a service
 * although the current user
 * would have permission to perform this action.
 * @package eZ\Publish\API\Repository\Exceptions
 */
abstract class ForbiddenException extends RuntimeException
{
    /**
     * returns an additional error code which indicates why an action could not be performed
     * @return integer an error code
     */
    abstract function getErrorCode();
}
