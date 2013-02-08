<?php
/**
 * File containing the Generator base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
        $this->isEmpty = true;
    }

    /**
     * Start document
     *
     * @param mixed $data
     */
    abstract public function startDocument( $data );

    /**
     * Returns if the document is empty or already contains data
     *
     * @return boolean
     */
    abstract public function isEmpty();

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
     *
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
     * Start object element
     *
     * @param string $name
     * @param string $mediaTypeName
     */
    abstract public function startObjectElement( $name, $mediaTypeName = null );

    /**
     * Check start object element
     *
     * @param mixed $data
     */
    protected function checkStartObjectElement( $data )
    {
        $this->checkStart( 'objectElement', $data, array( 'document', 'objectElement', 'hashElement', 'list' ) );

        $last = count( $this->stack ) - 2;
        if ( $this->stack[$last][0] !== 'list' )
        {
            // Ensure object element type only occurs once outside of lists
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
     * End object element
     *
     * @param string $name
     */
    abstract public function endObjectElement( $name );

    /**
     * Check end object element
     *
     * @param mixed $data
     */
    protected function checkEndObjectElement( $data )
    {
        $this->checkEnd( 'objectElement', $data );
    }

    /**
     * Start hash element
     *
     * @param string $name
     */
    abstract public function startHashElement( $name );

    /**
     * Check start hash element
     *
     * @param mixed $data
     */
    protected function checkStartHashElement( $data )
    {
        $this->checkStart( 'hashElement', $data, array( 'document', 'objectElement', 'hashElement', 'list' ) );

        $last = count( $this->stack ) - 2;
        if ( $this->stack[$last][0] !== 'list' )
        {
            // Ensure hash element type only occurs once outside of lists
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
     * End hash element
     *
     * @param string $name
     */
    abstract public function endHashElement( $name );

    /**
     * Check end hash element
     *
     * @param mixed $data
     */
    protected function checkEndHashElement( $data )
    {
        $this->checkEnd( 'hashElement', $data );
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
        $this->checkStart( 'valueElement', $data, array( 'objectElement', 'hashElement', 'list' ) );
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
        $this->checkStart( 'list', $data, array( 'objectElement', 'hashElement' ) );
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
        $this->checkStart( 'attribute', $data, array( 'objectElement', 'hashElement' ) );
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
     *
     * @return string
     */
    abstract public function getMediaType( $name );

    /**
     * Generates a media type from $name and $type
     *
     * @param string $name
     * @param string $type
     *
     * @return string
     */
    protected function generateMediaType( $name, $type )
    {
        return "application/vnd.ez.api.{$name}+{$type}";
    }

    /**
     * Generates a generic representation of the scalar, hash or list given in
     * $hashValue into the document, using an element of $hashElementName as
     * its parent
     *
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    abstract public function generateFieldTypeHash( $hashElementName, $hashValue );

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
