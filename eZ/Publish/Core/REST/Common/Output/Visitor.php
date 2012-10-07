<?php
/**
 * File containing the Visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Visitor for view models
 */
class Visitor
{
    /**
     * Visitors for value objects
     *
     * Structure:
     * <code>
     *  array(
     *      <class> => <ValueObjectVisitor>,
     *      …
     *  )
     *
     * @var array
     */
    protected $visitors = array();

    /**
     * Generator
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Generator
     */
    protected $generator;

    /**
     * HTTP Response Headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Mapping of status codes.
     *
     * @var array(int=>string)
     */
    public static $statusMap = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(reserviert)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URL Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'There are too many connections from your internet address',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    );

    /**
     * Construct from Generator and an array of concrete view model visitors
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param array $visitors
     */
    public function __construct( Generator $generator, array $visitors )
    {
        $this->generator = $generator;
        foreach ( $visitors as $class => $visitor )
        {
            $this->addVisitor( $class, $visitor );
        }
    }

    /**
     * Add a new visitor for the given class
     *
     * @param string $class
     * @param \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor $visitor
     */
    public function addVisitor( $class, ValueObjectVisitor $visitor )
    {
        if ( $class[0] === '\\' )
        {
            $class = substr( $class, 1 );
        }

        $this->visitors[$class] = $visitor;
    }

    /**
     * Set HTTP response header
     *
     * Does not allow overwriting of response headers. The first definition of
     * a header will be used.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader( $name, $value )
    {
        if ( !isset( $this->headers[$name] ) )
        {
            $this->headers[$name] = $value;
        }
    }

    /**
     * Sets the given status code in the corresponding header.
     *
     * Note that headers are generally not overwritten!
     *
     * @param int $statusCode
     */
    public function setStatus( $statusCode )
    {
        $this->statusCode = $statusCode;

        $status = sprintf(
            '%s %s',
            $statusCode,
            ( isset( self::$statusMap[$statusCode] )
                ? self::$statusMap[$statusCode]
                : 'Unknown' )
        );

        $this->setHeader( 'Status', $status );
    }

    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return \eZ\Publish\Core\REST\Common\Message
     */
    public function visit( $data )
    {

        $this->generator->reset();
        $this->generator->startDocument( $data );
        $this->visitValueObject( $data );

        //@TODO Needs refactoring!
        // A hackish solution to enable outer visitors to disable setting
        // of certain headers in inner visitors, for example Accept-Patch header
        // which is valid in GET/POST/PATCH for a resource, but must not appear
        // in the list of resources
        $filteredHeaders = array();
        foreach ( $this->headers as $headerName => $headerValue )
        {
            if ( $headerValue !== false )
            {
                $filteredHeaders[$headerName] = $headerValue;
            }
        }

        $result = new Message(
            $filteredHeaders,
            ( $this->generator->isEmpty()
                ? null
                : $this->generator->endDocument( $data ) ),
            $this->statusCode
        );

        $this->headers = array();

        return $result;
    }

    /**
     * Visit struct returned by controllers
     *
     * Should be called from sub-visitors to visit nested objects.
     *
     * @param mixed $data
     */
    public function visitValueObject( $data )
    {
        if ( !is_object( $data ) )
        {
            throw new Exceptions\InvalidTypeException( $data );
        }
        $checkedClassNames = array();

        $classname = get_class( $data );
        do {
            $checkedClassNames[] = $classname;
            if ( isset( $this->visitors[$classname] ) )
            {
                return $this->visitors[$classname]->visit( $this, $this->generator, $data );
            }
        } while ( $classname = get_parent_class( $classname ) );

        throw new Exceptions\NoVisitorFoundException( $checkedClassNames );
    }

    /**
     * Generates a media type for $type based on the used generator.
     *
     * @param string $type
     * @return string
     * @see \eZ\Publish\Core\REST\Common\Generator::getMediaType()
     */
    public function getMediaType( $type )
    {
        return $this->generator->getMediaType( $type );
    }
}
