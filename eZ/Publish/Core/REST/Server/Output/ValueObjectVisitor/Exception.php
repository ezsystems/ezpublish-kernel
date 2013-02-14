<?php
/**
 * File containing the Exception ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\UrlHandler;

/**
 * Exception value object visitor
 */
class Exception extends ValueObjectVisitor
{
    /**
     * Is debug mode enabled?
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Mapping of HTTP status codes to their respective error messages
     *
     * @var array
     */
    protected $httpStatusCodes = array(
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Time-out",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested range not satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        421 => "There are too many connections from your internet address",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        425 => "Unordered Collection",
        426 => "Upgrade Required",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Time-out",
        505 => "HTTP Version not supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        509 => "Bandwidth Limit Exceeded",
        510 => "Not Extended",
    );

    /**
     * Construct from debug flag
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param boolean $debug
     */
    public function __construct( UrlHandler $urlHandler, $debug = false )
    {
        parent::__construct( $urlHandler );
        $this->debug = (bool)$debug;
    }

    /**
     * Returns HTTP status code
     *
     * @return int
     */
    protected function getStatus()
    {
        return 500;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Exception $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ErrorMessage' );

        $statusCode = $this->getStatus();
        $visitor->setStatus( $statusCode );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ErrorMessage' ) );

        $generator->startValueElement( 'errorCode', $statusCode );
        $generator->endValueElement( 'errorCode' );

        $generator->startValueElement( 'errorMessage', $this->httpStatusCodes[$statusCode] );
        $generator->endValueElement( 'errorMessage' );

        $generator->startValueElement( 'errorDescription', $data->getMessage() );
        $generator->endValueElement( 'errorDescription' );

        if ( $this->debug )
        {
            $generator->startValueElement( 'trace', $data->getTraceAsString() );
            $generator->endValueElement( 'trace' );

            $generator->startValueElement( 'file', $data->getFile() );
            $generator->endValueElement( 'file' );

            $generator->startValueElement( 'line', $data->getLine() );
            $generator->endValueElement( 'line' );
        }

        $generator->endObjectElement( 'ErrorMessage' );
    }
}

