<?php
/**
 * File containing the InputDispatcher class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;
use eZ\Publish\API\REST\Server\Values;

/**
 * Input parsing dispatcher
 */
class InputDispatcher
{
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
    public function __construct( array $parsers = array() )
    {
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
     * Parse provided request
     *
     * @param string $contentType
     * @param string $body
     * @return mixed
     */
    public function parse( $contentType, $body )
    {
        if ( !isset( $this->parsers[$contentType] ) )
        {
            throw new \RuntimeException( "Received bullshitty content type." );
        }

        return $this->parsers[$contentType]->parse( $body );
    }
}

