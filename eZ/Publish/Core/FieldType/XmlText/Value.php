<?php
/**
 * File containing the XmlText Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;
use eZ\Publish\Core\FieldType\Value as BaseValue,
    eZ\Publish\Core\FieldType\XmlText\Input\Handler as InputHandler;

/**
 * Basic for TextLine field type
 */
class Value extends BaseValue
{
    /**
     * Text content
     *
     * @var string
     */
    public $text;

    /**
     * Input handler
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Input\Handler
     */
    private $inputHandler;

    /**
     * Initializes a new XmlText Value object with $text in
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Input\Handler $handler
     * @param string $text
     */
    public function __construct( InputHandler $handler, $text = '' )
    {
        $this->inputHandler = $handler;
        $this->text = $text;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }

    /**
     * Returns the input handler depending on the input value type
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Input\Handler
     */
    public function getInputHandler()
    {
        return $this->inputHandler;
    }

    /**
     * Sets the value for the XmlText to $text
     *
     * @param $text string
     */
    public function setText( $text )
    {
        $this->text = $text;
    }
}
