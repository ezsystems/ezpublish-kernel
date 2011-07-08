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

namespace ezp\base\Exception;

/**
 * Abstract Http Exception implementation
 *
 * Exceptions that map to any of the http errors should extend this class.
 *
 * @package ezp
 * @subpackage base
 */
abstract class AbstractHttp extends \RuntimeException implements \ezp\base\Exception
{
    const BAD_REQUEST       = 400;
    const UNAUTHORIZED      = 401;
    const PAYMENT_REQUIRED  = 402;
    const FORBIDDEN         = 403;
    const NOT_FOUND         = 404;
    const GONE              = 410;
    const INTERNAL_ERROR    = 500;
    const NOT_IMPLEMENTED   = 501;

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
            case self::GONE:
            case self::INTERNAL_ERROR:
            case self::NOT_IMPLEMENTED:
                parent::__construct( $message, $code, $previous );
                break;
            default:
                parent::__construct( "Non existing error code: '{$code}', message: '{$message}'", self::INTERNAL_ERROR, $previous );
        }
    }
}