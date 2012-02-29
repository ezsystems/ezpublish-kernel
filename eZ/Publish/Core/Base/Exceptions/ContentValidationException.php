<?php
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentValidationException as APIContentValidationException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
class ContentValidationException extends APIContentValidationException
{
    public function getErrorCode()
    {
        // @todo
    }
}
