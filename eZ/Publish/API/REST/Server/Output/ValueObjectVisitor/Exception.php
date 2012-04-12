<?php
/**
 * File containing the Exception visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Visitor;

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
     * COnstruct from debug flag
     *
     * @param mixed $debug
     * @return void
     */
    public function __construct( $debug = false )
    {
        $this->debug = (bool) $debug;
    }

    /**
     * Return HTTP status code
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
     * @param Visitor $visitor
     * @param Generator $generator
     * @param mixed $data
     * @return void
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startElement( 'ErrorMessage' );

        $statusCode = $this->getStatus();
        $visitor->setHeader( 'Status', $statusCode . ' ' . $this->httpStatusCodes[$statusCode] );
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

            $generator->startAttribute( 'file', $data->getFile() );
            $generator->endAttribute( 'file' );

            $generator->startAttribute( 'line', $data->getLine() );
            $generator->endAttribute( 'line' );
        }

        $generator->endElement( 'ErrorMessage' );
    }
}

