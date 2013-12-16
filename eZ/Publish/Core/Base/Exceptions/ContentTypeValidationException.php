<?php
/**
 * File containing the eZ\Publish\Core\Base\Exceptions\ContentTypeValidationException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentTypeValidationException as APIContentTypeValidationException;

/**
 * This Exception is thrown on create or update content type when content type is not valid
 */
class ContentTypeValidationException extends APIContentTypeValidationException
{
}
