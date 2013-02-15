<?php
/**
 * Contains Interface for exceptions that maps to http status codes
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

/**
 * Interface for exceptions that maps to http status codes
 *
 * The constants must be used as error code for this to be usable
 */
interface Httpable
{
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const CONFLICT = 409;
    const GONE = 410;

    const UNSUPPORTED_MEDIA_TYPE = 415;

    const INTERNAL_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const SERVICE_UNAVAILABLE = 503;
}
