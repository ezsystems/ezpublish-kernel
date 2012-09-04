<?php
/**
 * File containing the ParserTools class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Tools object to be used in Input Parsers
 */
class ParserTools
{
    /**
     * Creates a closure to load an object which will execute $method on
     * $service using $parameters
     *
     * @param object $service
     * @param string $method
     * @param array $parameters
     * @return Closure
     */
    public function createLoadingClosure( $service, $method, array $parameters )
    {
        return function() use ( $service, $method, $parameters )
        {
            return call_user_func_array( array( $service, $method ), $parameters );
        };
    }

    /**
     * Parses the given $objectElement, if it contains embedded data
     *
     * @param array $objectElement
     * @param ParsingDispatcher $parsingDispatcher
     * @return void
     */
    public function parseObjectElement( array $objectElement, ParsingDispatcher $parsingDispatcher )
    {
        if ( $this->isEmbeddedObject( $objectElement ) )
        {
            $parsingDispatcher->parse(
                $objectElement,
                $objectElement['_media-type']
            );
        }
    }

    /**
     * Returns if the given $objectElement has embedded object data or is only
     * a reference
     *
     * @param array $objectElement
     * @return bool
     */
    public function isEmbeddedObject( array $objectElement )
    {
        foreach ( $objectElement as $childKey => $childValue )
        {
            $childKeyIndicator = substr( $childKey, 0, 1 );
            if ( $childKeyIndicator !== '#' && $childKeyIndicator !== '_' )
            {
                return true;
            }
        }
        return false;
    }
}
