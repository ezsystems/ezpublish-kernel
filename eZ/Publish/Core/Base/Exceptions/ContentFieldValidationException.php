<?php
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException as APIContentFieldValidationException;

/**
 * This Exception is thrown on create or update content one or more given fields are not valid
 */
class ContentFieldValidationException extends APIContentFieldValidationException
{
    /**
     *
     * @return array
     */
    public function getFieldExceptions()
    {
        // @todo Implement or remove
    }
}
