<?php
/**
 * File containing the Generator base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output;

/**
 * Output generator
 */
abstract class Generator
{
    /**
     * Generator creation stack
     *
     * Use to check if it is OK to start / close the requested element in the
     * current state.
     *
     * @var array
     */
    protected $stack = array();

    /**
     * Reset output visitor to a virgin state
     */
    public function reset()
    {
        $this->stack = array();
    }

    /**
     * Start document
     *
     * @param mixed $data
     */
    abstract public function startDocument( $data );

    /**
     * Check start document
     *
     * @param mixed $data
     */
    protected function checkStartDocument( $data )
    {
        if ( count( $this->stack ) )
        {
            throw new Exceptions\OutputGeneratorException(
                "Starting a document may only be the very first operation."
            );
        }

        $this->stack[] = array( 'document', $data, array() );
    }

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
     * Check end document
     *
     * @param mixed $data
     */
    protected function checkEndDocument( $data )
    {
        $this->checkEnd( 'document', $data );
    }

    /**
     * Start element
     *
     * @param string $name
     * @param string $mediaTypeName
     */
    abstract public function startElement( $name, $mediaTypeName = null );

    /**
     * Check start element
     *
     * @param mixed $data
     */
    protected function checkStartElement( $data )
    {
        $this->checkStart( 'element', $data, array( 'document', 'element', 'list' ) );

        $last = count( $this->stack ) - 2;
        if ( $this->stack[$last][0] !== 'list' )
        {
            // Ensure element type only occurs once outside of lists
            if ( isset( $this->stack[$last][2][$data] ) )
            {
                throw new Exceptions\OutputGeneratorException(
                    "Element {$data} may only occur once inside of {$this->stack[$last][0]}."
                );
            }
        }
        $this->stack[$last][2][$data] = true;
    }

    /**
     * End element
     *
     * @param string $name
     */
    abstract public function endElement( $name );

    /**
     * Check end element
     *
     * @param mixed $data
     */
    protected function checkEndElement( $data )
    {
        $this->checkEnd( 'element', $data );
    }

    /**
     * Start value element
     *
     * @param string $name
     * @param string $value
     */
    abstract public function startValueElement( $name, $value );

    /**
     * Check start value element
     *
     * @param mixed $data
     */
    protected function checkStartValueElement( $data )
    {
        $this->checkStart( 'valueElement', $data, array( 'element' ) );
    }

    /**
     * End value element
     *
     * @param string $name
     */
    abstract public function endValueElement( $name );

    /**
     * Check end value element
     *
     * @param mixed $data
     */
    protected function checkEndValueElement( $data )
    {
        $this->checkEnd( 'valueElement', $data );
    }

    /**
     * Start list
     *
     * @param string $name
     */
    abstract public function startList( $name );

    /**
     * Check start list
     *
     * @param mixed $data
     */
    protected function checkStartList( $data )
    {
        $this->checkStart( 'list', $data, array( 'element' ) );
    }

    /**
     * End list
     *
     * @param string $name
     */
    abstract public function endList( $name );

    /**
     * Check end list
     *
     * @param mixed $data
     */
    protected function checkEndList( $data )
    {
        $this->checkEnd( 'list', $data );
    }

    /**
     * Start attribute
     *
     * @param string $name
     * @param string $value
     */
    abstract public function startAttribute( $name, $value );

    /**
     * Check start attribute
     *
     * @param mixed $data
     */
    protected function checkStartAttribute( $data )
    {
        $this->checkStart( 'attribute', $data, array( 'element' ) );
    }

    /**
     * End attribute
     *
     * @param string $name
     */
    abstract public function endAttribute( $name );

    /**
     * Check end attribute
     *
     * @param mixed $data
     */
    protected function checkEndAttribute( $data )
    {
        $this->checkEnd( 'attribute', $data );
    }

    /**
     * Get media type
     *
     * @param string $name
     * @return string
     */
    abstract public function getMediaType( $name );

    /**
     * Generates a media type from $name and $type
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function generateMediaType( $name, $type )
    {
        return "application/vnd.ez.api.{$name}+{$type}";
    }

    /**
     * Check close / end operation
     *
     * @param string $type
     * @param mixed $data
     * @param array $validParents
     */
    protected function checkStart( $type, $data, array $validParents )
    {
        $lastTag = end( $this->stack );

        if ( !is_array( $lastTag ) )
        {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    "Invalid start: Trying to open outside of a document."
                )
            );
        }

        if ( !in_array( $lastTag[0], $validParents ) )
        {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    "Invalid start: Trying to open %s inside %s, valid parent nodes are: %s.",
                    $type,
                    $lastTag[0],
                    implode( ', ', $validParents )
                )
            );
        }

        $this->stack[] = array( $type, $data, array() );
    }

    /**
     * Check close / end operation
     *
     * @param string $type
     * @param mixed $data
     */
    protected function checkEnd( $type, $data )
    {
        $lastTag = array_pop( $this->stack );

        if ( !is_array( $lastTag ) )
        {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    "Invalid close: Trying to close on empty stack."
                )
            );
        }

        if ( array( $lastTag[0], $lastTag[1] ) !== array( $type, $data ) )
        {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    "Invalid close: Trying to close %s:%s, while last element was %s:%s.",
                    $type,
                    $data,
                    $lastTag[0],
                    $lastTag[1]
                )
            );
        }
    }
}

