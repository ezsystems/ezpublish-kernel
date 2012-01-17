<?php
namespace ezp\PublicAPI\Interfaces\Exception;

use ezp\PublicAPI\Interfaces\Exception;

/**
 * An Exception which is thrown if an operation cannot be performed by a service
 * although the current user
 * would have permission to perform this action.
 * @package ezp\PublicAPI\Interfaces
 */
abstract class ForbiddenException extends RuntimeException implements Exception {
    
    /**
     * returns an additional error code which indicates why an action could not be performed
     * @return integer an error code
     */
    abstract function getErrorCode();
}
