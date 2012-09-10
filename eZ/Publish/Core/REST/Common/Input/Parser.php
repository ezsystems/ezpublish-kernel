<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Input;

/**
 * Base class for input parser
 */
abstract class Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract public function parse( array $data, ParsingDispatcher $parsingDispatcher );

    /**
     * Parses a translatable list, like names or descriptions
     *
     * @param array $listElement
     * @return array
     */
    protected function parseTranslatableList( array $listElement )
    {
        $listItems = array();
        foreach ( $listElement['value'] as $valueRow )
        {
            $listItems[$valueRow['_languageCode']] = $valueRow['#text'];
        }
        return $listItems;
    }

    /**
     * Parses a boolean from $stringValue
     *
     * @param string $stringValue
     * @return bool
     */
    protected function parseBooleanValue( $stringValue )
    {
        switch ( strtolower( $stringValue ) )
        {
            case 'true':
                return true;
            case 'false':
                return false;
        }

        throw new RuntimeException( "Unknown boolean value '{$stringValue}'." );
    }
}
