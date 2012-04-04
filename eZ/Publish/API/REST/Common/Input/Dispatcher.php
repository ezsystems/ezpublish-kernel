<?php
/**
 * File containing the Input Dispatcher class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Input;

/**
 * Input parsing dispatcher
 */
class Dispatcher
{
    /**
     * Array of handlers
     *
     * Structure:
     *
     * <code>
     *  array(
     *      <type> => <handler>,
     *      …
     *  )
     * </code>
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Array of parsers
     *
     * Structure:
     *
     * <code>
     *  array(
     *      <contentType> => <parser>,
     *      …
     *  )
     * </code>
     *
     * @var array
     */
    protected $parsers = array();

    /**
     * Construct from optional parsers array
     *
     * @param array $parsers
     * @return void
     */
    public function __construct( array $handlers = array(), array $parsers = array() )
    {
        foreach ( $handlers as $type => $handler )
        {
            $this->addHandler( $type, $handler );
        }
        foreach ( $parsers as $contentType => $parser )
        {
            $this->addParser( $contentType, $parser );
        }
    }

    /**
     * Add another parser for the given Content Type
     *
     * @param string $contentType
     * @param Parser $parser
     * @return void
     */
    public function addParser( $contentType, Parser $parser )
    {
        $this->parsers[$contentType] = $parser;
    }

    /**
     * Add another handler for the given Content Type
     *
     * @param string $type
     * @param Handler $handler
     * @return void
     */
    public function addHandler( $type, Handler $handler )
    {
        $this->handlers[$type] = $handler;
    }

    /**
     * Parse provided request
     *
     * @param string $contentType
     * @param string $body
     * @return mixed
     */
    public function parse( $contentType, $body )
    {
        $contentTypeParts = explode( '+', $contentType );
        if ( count( $contentTypeParts ) !== 2 )
        {
            throw new \RuntimeException( "No format specification in content type. Missing '+(json|xml|…)' in '{$contentType}'." );
        }

        $media  = $contentTypeParts[0];
        $format = $contentTypeParts[1];

        if ( !isset( $this->handlers[$format] ) )
        {
            throw new \RuntimeException( "Unknown format specification: '{$format}'." );
        }
        if ( !isset( $this->parsers[$media] ) )
        {
            throw new \RuntimeException( "Unknown content type specification: '{$media}'." );
        }

        return $this->parsers[$media]->parse(
            $this->handlers[$format]->convert( $body )
        );
    }
}

