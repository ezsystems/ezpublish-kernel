<?php
/**
 * File containing the XML generator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Generator;

/**
 * Xml generator
 */
class Xml extends Generator
{
    /**
     * XMLWriter
     *
     * @var \XMLWriter
     */
    protected $xmlWriter;

    /**
     * Start document
     *
     * @param mixed $data
     * @return void
     */
    public function startDocument( $data )
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument( '1.0', 'UTF-8' );
    }

    /**
     * End document
     *
     * Returns the generated document as a string.
     *
     * @param mixed $data
     * @return string
     */
    public function endDocument( $data )
    {
        $this->xmlWriter->endDocument();
        return $this->xmlWriter->outputMemory();
    }

    /**
     * Start element
     *
     * @param string $name
     * @return void
     */
    public function startElement( $name )
    {
        $this->xmlWriter->startElement( $name );

        $this->startAttribute( "media-type", $this->getMediaType( $name ) );
        $this->endAttribute( "media-type" );
    }

    /**
     * End element
     *
     * @param string $name
     * @return void
     */
    public function endElement( $name )
    {
        $this->xmlWriter->endElement();
    }

    /**
     * Start value element
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function startValueElement( $name, $value )
    {
        $this->xmlWriter->startElement( $name );
        $this->xmlWriter->text( $value );
    }

    /**
     * End value element
     *
     * @param string $name
     * @return void
     */
    public function endValueElement( $name )
    {
        $this->xmlWriter->endElement();
    }

    /**
     * Start list
     *
     * @param string $name
     * @return void
     */
    public function startList( $name )
    {
    }

    /**
     * End list
     *
     * @param string $name
     * @return void
     */
    public function endList( $name )
    {
    }

    /**
     * Start attribute
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function startAttribute( $name, $value )
    {
        $this->xmlWriter->startAttribute( $name );
        $this->xmlWriter->text( $value );
    }

    /**
     * End attribute
     *
     * @param string $name
     * @return void
     */
    public function endAttribute( $name )
    {
        $this->xmlWriter->endAttribute();
    }

    /**
     * Get media type
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    public function getMediaType( $name, $type = 'xml' )
    {
        return parent::getMediaType( $name, $type );
    }
}

