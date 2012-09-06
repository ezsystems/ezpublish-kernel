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
        return $objectElement['_href'];
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
