<?php
/**
 * This file contains the Aggregate converter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use DOMDocument;

/**
 * Aggregate converter converts using configured converters in succession.
 */
class Aggregate implements Converter
{
    /**
     * An array of converters.
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    protected $converters = array();

    /**
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter[] $converters
     */
    public function __construct( array $converters )
    {
        foreach ( $converters as $converter )
        {
            $this->addConverter( $converter );
        }
    }

    /**
     * Registers converter.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter $converter
     */
    public function addConverter( Converter $converter )
    {
        $this->converters[] = $converter;
    }

    /**
     * Performs conversion of the given $document using configured converters.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $document )
    {
        foreach ( $this->converters as $converter )
        {
            $document = $converter->convert( $document );
        }

        return $document;
    }
}
