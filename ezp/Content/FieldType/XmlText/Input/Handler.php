<?php
/**
 * File containing the ezp\Content\FieldType\XmlText\Input\Handler\Simplified class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText\Input;

use ezp\Content\FieldType\XmlText\Input\Parser as InputParserInterface,
    ezp\Content\FieldType\XmlText\Input\Parser\Base as BaseInputParser,
    DOMDocument;

/**
 * Simplified XmlText input handler
 */
class Handler
{
    /**
     * Construct a new Simplified InputHandler$xmlString
     *
     * @param string $xmlString
     * @param \ezp\Content\FieldType\XmlText\Input\Parser Parser
     */
    public function __construct( Parser $parser )
    {
        $this->parser = $parser;
        // $this->xmlString = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xmlString, -1, $count );
        /*if ( $count > 0 )
           {
           eZDebug::writeWarning( "$count invalid character(s) detected. They have been removed from input.", __METHOD__ );
           }*/
    }

    /**
     * Checks if $xmlString is a valid XML
     * @param string $xmlString
     * @return bool
     */
    public function isXmlValid( $xmlString )
    {
        $this->parser->setOption( BaseInputParser::OPT_VALIDATE_ERROR_LEVEL, BaseInputParser::ERROR_ALL );
        $this->parser->setOption( BaseInputParser::OPT_DETECT_ERROR_LEVEL, BaseInputParser::ERROR_ALL );
        $this->parser->setOption( BaseInputParser::OPT_PARSE_LINE_BREAKS, false );
        $this->parser->setOption( BaseInputParser::OPT_REMOVE_DEFAULT_ATTRS, false );

        $document = $this->parser->process( $xmlString );

        // @todo instanceof
        if ( !is_object( $document ) )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns the last parsing messages (from the last parsing operation, {@see isXmlValid}, {@see process})
     * @return array
     */
    public function getParsingMessages()
    {
        return $this->parser->getMessages();
    }

    /**
     * Processes $xmlString and indexes the external data it references
     * @return bool
     */
    public function process( $xmlString )
    {
        $document = $this->parser->process( $xmlString );

        return !$document instanceof DOMDocument;
    }

    /**
     * Callback that gets a location from its id
     * @param mixed $locationId
     * @return \ezp\Content\Location
     */
    public function getLocationById( $locationId )
    {
        return false;
    }

    /**
     * Callback that gets a location from its path
     * @param string $locationPath
     * @return \ezp\Content\Location
     */
    public function getLocationByPath( $locationPath )
    {
        return false;
    }

    /**
     * Callback that gets a content from its id
     * @param int $contentId
     * @return \ezp\Content
     */
    public function getContentById( $contentId )
    {
        return false;
    }

    /**
     * Registers an external URL
     * @param string $url
     * @return Url
     * @todo Implement & Document
     */
    public function registerUrl( $url )
    {
        return false;
    }

    /**
     * XmlText parser
     * @var \ezp\Content\FieldType\XmlText\Input\Parser
     */
    protected $parser;
}
?>