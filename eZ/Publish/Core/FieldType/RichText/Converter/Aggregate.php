<?php
/**
 * This file contains the Aggregate converter class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\Converter;

use eZ\Publish\Core\FieldType\RichText\Converter;
use DOMDocument;

/**
 * Aggregate converter converts using configured converters in prioritized order.
 */
class Aggregate implements Converter
{
    /**
     * An array of converters, sorted by priority.
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Converter[]
     */
    protected $converters = array();

    /**
     * @param mixed $converters An array of Converters, sorted by priority
     */
    public function __construct( array $converters = array() )
    {
        $this->converters = $converters;
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
