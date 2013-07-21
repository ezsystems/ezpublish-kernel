<?php
/**
 * This file contains the ValidatorDispatcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\XmlText\Validator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DOMDocument;

/**
 * Dispatcher for various validators depending on the XML document namespace.
 */
class ValidatorDispatcher
{
    /**
     * Mapping of namespaces to validators.
     *
     * @var \eZ\Publish\Core\FieldType\XmlText\Validator[]
     */
    protected $mapping = array();

    /**
     * @param \eZ\Publish\Core\FieldType\XmlText\Validator[] $converterMap
     */
    public function __construct( $converterMap )
    {
        foreach ( $converterMap as $namespace => $validator )
        {
            $this->addValidator( $namespace, $validator );
        }
    }

    /**
     * Adds validator mapping.
     *
     * @param string $namespace
     * @param \eZ\Publish\Core\FieldType\XmlText\Validator $validator
     */
    public function addValidator( $namespace, Validator $validator )
    {
        $this->mapping[$namespace] = $validator;
    }

    /**
     * Dispatches DOMDocument to the namespace mapped validator.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param \DOMDocument $document
     *
     * @return string[]
     */
    public function dispatch( DOMDocument $document )
    {
        $documentNamespace = $document->documentElement->lookupNamespaceURI( null );
        // checking for null as ezxml has no default namespace...
        if ( $documentNamespace === null )
        {
            $documentNamespace = $document->documentElement->lookupNamespaceURI( "xhtml" );
        }

        foreach ( $this->mapping as $namespace => $validator )
        {
            if ( $documentNamespace === $namespace )
            {
                return $validator->validate( $document );
            }
        }

        // @todo add schema for ezxhtml5
        if ( $documentNamespace === "http://ez.no/namespaces/ezpublish5/xhtml5" )
        {
            return array();
        }

        throw new NotFoundException( "Validator", $documentNamespace );
    }
}
