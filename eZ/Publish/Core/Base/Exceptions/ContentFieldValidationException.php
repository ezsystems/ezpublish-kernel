<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
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
