<?php
/**
 * Contains Abstract Http Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * Abstract Http Exception implementation
 *
 * Exceptions that map to any of the http errors should extend this class.
 *
 * @package ezp
 * @subpackage base
 */
abstract class AbstractHttp extends \RuntimeException implements \ezp\Base\Exception
{
    const BAD_REQUEST        = 400;
    const UNAUTHORIZED       = 401;
    const PAYMENT_REQUIRED   = 402;
    const FORBIDDEN          = 403;
    const NOT_FOUND          = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE     = 406;
    const CONFLICT           = 409;
    const GONE               = 410;

    const UNSUPPORTED_MEDIA_TYPE = 415;

    const INTERNAL_ERROR      = 500;
    const NOT_IMPLEMENTED     = 501;
    const SERVICE_UNAVAILABLE = 503;

    /**
     * @param string $message
     * @param int $code Must be one of the available constants on this class
     * @param \Exception|null $previous
     */
    public function __construct( $message, $code, \Exception $previous = null )
    {
        switch ( $code )
        {
            case self::BAD_REQUEST:
            case self::UNAUTHORIZED:
            case self::PAYMENT_REQUIRED:
            case self::FORBIDDEN:
            case self::NOT_FOUND:
            case self::METHOD_NOT_ALLOWED:
            case self::NOT_ACCEPTABLE:
            case self::CONFLICT:
            case self::GONE:
            case self::UNSUPPORTED_MEDIA_TYPE:
            case self::INTERNAL_ERROR:
            case self::NOT_IMPLEMENTED:
            case self::SERVICE_UNAVAILABLE:
                parent::__construct( $message, $code, $previous );
                break;
            default:
                parent::__construct( "Non existing error code: '{$code}', with message: '{$message}'", self::INTERNAL_ERROR, $previous );
        }
    }
}
