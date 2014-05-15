<?php
/**
 * File containing the Parsing Dispatcher class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Input;

use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parsing dispatcher
 */
class ParsingDispatcher
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
     * @var \eZ\Publish\Core\REST\Common\Input\Parser[]
     */
    protected $parsers = array();

    /**
     * Construct from optional parsers array
     *
     * @param array $parsers
     */
    public function __construct( array $parsers = array() )
    {
        foreach ( $parsers as $contentType => $parser )
        {
            $this->addParser( $contentType, $parser );
        }
    }

    /**
     * Adds another parser for the given Content Type
     *
     * @param string $contentType
     * @param \eZ\Publish\Core\REST\Common\Input\Parser $parser
     */
    public function addParser( $contentType, Parser $parser )
    {
        $this->parsers[$contentType] = $parser;
    }

    /**
     * Parses the given $data according to $mediaType
     *
     * @param array $data
     * @param string $mediaType
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function parse( array $data, $mediaType )
    {
        // Remove encoding type
        if ( ( $plusPos = strrpos( $mediaType, '+' ) ) !== false )
        {
            $mediaType = substr( $mediaType, 0, $plusPos );
        }

        if ( !isset( $this->parsers[$mediaType] ) )
        {
            throw new Exceptions\Parser( "Unknown content type specification: '{$mediaType}'." );
        }
        return $this->parsers[$mediaType]->parse( $data, $this );
    }
}
