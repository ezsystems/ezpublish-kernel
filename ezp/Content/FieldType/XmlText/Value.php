<?php
/**
 * File containing the XmlText Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Content\FieldType\XmlText\Input\Handler as InputHandler,
    ezp\Content\FieldType\XmlText\Input\Parser\Simplified as SimplifiedInputParser,
    ezp\Content\FieldType\XmlText\Input\Parser\OnlineEditor as OnlineEditorParser,
    ezp\Content\FieldType\XmlText\Input\Parser\Raw as RawInputParser;

/**
 * Basic, raw value for TextLine field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Text content
     *
     * @var string
     */
    public $text;

    /**
     * Text content, as RAW format (self::INPUT_FORMAT_RAW)
     * @var string
     */
    public $rawText;

    /**
     * Text input format
     * @type string One of the INPUT_FORMAT_* class constants
     */
    private $inputFormat;

    /**
     * Input handler
     * @var \ezp\Content\FieldType\XmlText\Input\Handler
     */
    private $inputHandler;

    /**
     * Input parser
     * \ezp\Content\FieldType\XmlText\Input\Parser
     */
    private $inputParser;

    /**
     * HTML input format constant
     */
    // const INPUT_FORMAT_HTML = 'html';

    /**
     * Plain text input format constant
     */
    const INPUT_FORMAT_PLAIN = 'plain';

    /**
     * RAW input format constant
     * Internal storage format
     */
    const INPUT_FORMAT_RAW = 'raw';

    /**
     * Input parsing classes mapping
     * @var array(string=>string)
     */
    private $parserClasses = array(
        'raw'   => 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\Raw',
        'plain' => 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\Simplified',
        // 'html'  => 'ezp\\Content\\FieldType\\XmlText\\Input\\Parser\\OnlineEditor',
    );

    /**
     * Initializes a new XmlText Value object with $text in
     *
     * @param string $text
     * @param string $type Which format the input is provided as. Expects one of the FORMAT_* class constants
     */
    public function __construct( $text = '', $inputFormat = self::INPUT_FORMAT_PLAIN )
    {
        $this->text = $text;
        if ( $inputFormat === self::INPUT_FORMAT_RAW )
            $this->rawText = $text;
        $this->inputFormat = $inputFormat;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        throw new \RuntimeException( 'Implement this method' );
    }

    /**
     * Returns the input handler depending on the input value type
     * @return \ezp\Content\FieldType\XmlText\Input\Handler
     */
    public function getInputHandler()
    {
        if ( $this->inputHandler === null )
        {
            $this->inputHandler = new InputHandler( $this->getInputParser() );
        }
        return $this->inputHandler;
    }

    /**
     * Returns the XML Input Parser for an XmlText Value
     * @param \ezp\Content\FieldType\XmlText\Value $value
     * @return \ezp\Content\FieldType\XmlText\Input\Parser
     */
    protected function getInputParser()
    {
        if ( $this->inputParser === null )
        {
            // @todo Load from configuration
            if ( !isset( $this->parserClasses[$this->inputFormat] ) )
            {
                // @todo Use dedicated exception
                throw new \Exception( "No parser found for input format '{$this->inputFormat}'" );
            }

            $this->inputParser = new $this->parserClasses[$this->inputFormat];
        }
        return $this->inputParser;
    }

    /**
     * Sets the raw value for the XmlText to $rawText
     * @var text
     */
    public function setRawText( $rawText )
    {
        $this->rawText = $rawText;
    }
}
