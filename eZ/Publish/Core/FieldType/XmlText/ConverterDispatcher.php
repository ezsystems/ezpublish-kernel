<?php
/**
 * This file contains the ConverterDispatcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\XmlText\Converter;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DOMDocument;

/**
 * Dispatcher for various converters depending on the XML document namespace.
 */
class ConverterDispatcher
{
    /**
     * Mapping of namespaces to converters.
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Converter[]
     */
    protected $mapping = array();

    /**
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter[] $converterMap
     */
    public function __construct( $converterMap )
    {
        foreach ( $converterMap as $namespace => $converter )
        {
            $this->addConverter( $namespace, $converter );
        }
    }

    /**
     * Adds converter mapping.
     *
     * @param string $namespace
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter $converter
     */
    public function addConverter( $namespace, Converter $converter )
    {
        $this->mapping[$namespace] = $converter;
    }

    /**
     * Dispatches DOMDocument to the namespace mapped converter.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param \DOMDocument $document
     *
     * @return \DOMDocument
     */
    public function dispatch( DOMDocument $document )
    {
        $documentNamespace = $document->documentElement->lookupNamespaceURI( null );
        // checking for null as ezxml has no default namespace...
        if ( $documentNamespace === null )
        {
            $documentNamespace = $document->documentElement->lookupNamespaceURI( "xhtml" );
        }

        foreach ( $this->mapping as $namespace => $converter )
        {
            if ( $documentNamespace === $namespace )
            {
                return $converter->convert( $document );
            }
        }

        throw new NotFoundException( "Converter", $documentNamespace );
    }
}
