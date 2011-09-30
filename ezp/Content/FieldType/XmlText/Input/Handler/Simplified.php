<?php
/**
 * File containing the ezp\Content\FieldType\XmlText\Input\Handler\Simplified class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText\Input\Handler;

use ezp\Content\FieldType\XmlText\Input\Handler as InputHandler,
    ezp\Content\FieldType\XmlText\Input\Parser\Simplified as InputParser,
    DOMDocument;

/**
 * Simplified XmlText input handler
 */
class Simplified implements InputHandler
{
    /**
     * Construct a new Simplified InputHandler$xmlString
     *
     * @param string $xmlString
     */
    public function __construct( $xmlString )
    {
        $this->xmlString = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xmlString, -1, $count );
        /*if ( $count > 0 )
        {
            eZDebug::writeWarning( "$count invalid character(s) detected. They have been removed from input.", __METHOD__ );
        }*/
    }

    /**
     * Checks if XML is valid
     * @return bool
     */
    public function isXmlValid()
    {
        $parser = new InputParser( true, InputParser::ERROR_ALL, true );
        $document = $parser->process( $this->xmlString );

        // @todo instanceof
        if ( !is_object( $document ) )
        {
            // $errorMessage = implode( ' ', $this->parser->getMessages() );
            // throw an exception that gets catched by the caller
            // $this->setValidationError( $errorMessage );
            return false;
        }

        return true;
    }

    /**
     * Processes the XML string and indexes the external data it references
     * @return bool
     */
    public function process()
    {
        $parser = new InputParser();
        $document = $parser->process( $this->xmlString );

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
     * XmlText string
     * @var string
     */
    protected $xmlString;
}
?>