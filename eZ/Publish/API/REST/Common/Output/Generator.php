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
     * Generator creation stack
     *
     * Use to check if it is OK to start / close the requested element in the
     * current state.
     *
     * @var array
     */
    protected $stack = array();

    /**
     * Start document
     *
     * @param mixed $data
     * @return void
     */
    abstract public function startDocument( $data );

    /**
     * Check start document
     *
     * @param mixed $data
     * @return void
     */
    protected function checkStartDocument( $data )
    {
        if ( count( $this->stack ) )
        {
            throw new Exceptions\OutputGeneratorException(
                "Starting a document may only be the very first opertation."
            );
        }

        $this->stack[] = array( 'document', $data );
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
     * @return void
     */
    protected function checkEndDocument( $data )
    {
        $this->checkEnd( 'document', $data );
    }

    /**
     * Start element
     *
     * @param string $name
     * @return void
     */
    abstract public function startElement( $name );

    /**
     * Check start element
     *
     * @param mixed $data
     * @return void
     */
    protected function checkStartElement( $data )
    {
        $this->checkStart( 'element', $data, array( 'document', 'list' ) );
    }

    /**
     * End element
     *
     * @param string $name
     * @return void
     */
    abstract public function endElement( $name );

    /**
     * Check end element
     *
     * @param mixed $data
     * @return void
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
     * @return void
     */
    abstract public function startValueElement( $name, $value );

    /**
     * Check start value element
     *
     * @param mixed $data
     * @return void
     */
    protected function checkStartValueElement( $data )
    {
        $this->checkStart( 'valueElement', $data, array( 'element' ) );
    }

    /**
     * End value element
     *
     * @param string $name
     * @return void
     */
    abstract public function endValueElement( $name );

    /**
     * Check end value element
     *
     * @param mixed $data
     * @return void
     */
    protected function checkEndValueElement( $data )
    {
        $this->checkEnd( 'valueElement', $data );
    }

    /**
     * Start list
     *
     * @param string $name
     * @return void
     */
    abstract public function startList( $name );

    /**
     * Check start list
     *
     * @param mixed $data
     * @return void
     */
    protected function checkStartList( $data )
    {
        $this->checkStart( 'list', $data, array( 'element' ) );
    }

    /**
     * End list
     *
     * @param string $name
     * @return void
     */
    abstract public function endList( $name );

    /**
     * Check end list
     *
     * @param mixed $data
     * @return void
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
     * @return void
     */
    abstract public function startAttribute( $name, $value );

    /**
     * Check start attribute
     *
     * @param mixed $data
     * @return void
     */
    protected function checkStartAttribute( $data )
    {
        $this->checkStart( 'attribute', $data, array( 'element' ) );
    }

    /**
     * End attribute
     *
     * @param string $name
     * @return void
     */
    abstract public function endAttribute( $name );

    /**
     * Check end attribute
     *
     * @param mixed $data
     * @return void
     */
    protected function checkEndAttribute( $data )
    {
        $this->checkEnd( 'attribute', $data );
    }

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

    /**
     * Check close / end operation
     *
     * @param string $type
     * @param mixed $data
     * @param array $validParents
     * @return void
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

        $this->stack[] = array( $type, $data );
    }

    /**
     * Check close / end operation
     *
     * @param string $type
     * @param mixed $data
     * @return void
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

        if ( $lastTag !== array( $type, $data ) )
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

