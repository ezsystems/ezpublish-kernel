<?php
/**
 * File containing the ContentObjectStates parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\Core\REST\Common\Values\RestObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;

/**
 * Parser for ContentObjectStates
 */
class ContentObjectStates extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState[]
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        // @todo XSD says that no ObjectState elements is valid,
        // but we should at least have one if setting new states to content?
        if ( !array_key_exists( 'ObjectState', $data ) || !is_array( $data['ObjectState'] ) || empty( $data['ObjectState'] ) )
        {
            throw new Exceptions\Parser( "Missing or invalid 'ObjectState' elements for ContentObjectStates." );
        }

        $states = array();
        foreach ( $data['ObjectState'] as $rawStateData )
        {
            if ( !array_key_exists( '_href', $rawStateData ) )
            {
                throw new Exceptions\Parser( "Missing '_href' attribute for ObjectState." );
            }

            $values = $this->urlHandler->parse( 'objectstate', $rawStateData['_href'] );

            $states[] = new RestObjectState(
                new ObjectState(
                    array(
                        'id' => $values['objectstate']
                    )
                ),
                $values['objectstategroup']
            );
        }

        return $states;
    }
}
