<?php
/**
 * File containing the Generator base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output;

/**
 * Output generator
 */
abstract class Generator
{
    /**
     * Start document
     *
     * @param mixed $data
     * @return void
     */
    abstract public function startDocument( $data );

    /**
     * End document
     *
     * Returns the generated document as a string.
     *
     * @param mixed $data
     * @return string
     */
    abstract public function endDocument( $data );

    /**
     * Start element
     *
     * @param string $name
     * @return void
     */
    abstract public function startElement( $name );

    /**
     * End element
     *
     * @param string $name
     * @return void
     */
    abstract public function endElement( $name );

    /**
     * Start value element
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    abstract public function startValueElement( $name, $value );

    /**
     * End value element
     *
     * @param string $name
     * @return void
     */
    abstract public function endValueElement( $name );

    /**
     * Start list
     *
     * @param string $name
     * @return void
     */
    abstract public function startList( $name );

    /**
     * End list
     *
     * @param string $name
     * @return void
     */
    abstract public function endList( $name );

    /**
     * Start attribute
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    abstract public function startAttribute( $name, $value );

    /**
     * End attribute
     *
     * @param string $name
     * @return void
     */
    abstract public function endAttribute( $name );

    /**
     * Get media type
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    public function getMediaType( $name, $type )
    {
        return "application/vnd.ez.api.{$name}+{$type}";
    }
}

