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
 * Aggregate converter converts using configured converters in prioritized order.
 */
class Aggregate implements Converter
{
    /**
     * An array of arrays of converters, indexed by priority.
     *
     * @var mixed
     */
    protected $convertersByPriority = array();

    /**
     * Indicates if the array of converters is sorted by priority.
     *
     * @var boolean
     */
    protected $areConvertersSorted = false;

    /**
     * @param mixed $converters An array of Converters with priorities
     */
    public function __construct( array $converters )
    {
        foreach ( $converters as $converter )
        {
            $this->addConverter( $converter["service"], $converter["priority"] );
        }
    }

    /**
     * Registers converter.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter $converter
     * @param int $priority
     */
    public function addConverter( Converter $converter, $priority )
    {
        $this->convertersByPriority[$priority][] = $converter;
        $this->areConvertersSorted = false;
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
        if ( !$this->areConvertersSorted )
        {
            ksort( $this->convertersByPriority );
            $this->areConvertersSorted = true;
        }

        foreach ( $this->convertersByPriority as $converters )
        {
            /** @var \eZ\Publish\Core\FieldType\XmlText\Converter $converter */
            foreach ( $converters as $converter )
            {
                $document = $converter->convert( $document );
            }
        }

        return $document;
    }
}
