<?php
namespace eZ\Publish\API\Repository\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ForbiddenException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
abstract class ContentFieldValidationException extends ForbiddenException
{
    /**
     * 
     * @return array 
     */
    public abstract function getFieldExceptions();
}


