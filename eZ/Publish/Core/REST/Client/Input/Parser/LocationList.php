<?php
/**
 * File containing the LocationList parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for LocationList
 */
class LocationList extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $locations = array();
        foreach ( $data['Location'] as $rawLocationData )
        {
            $locations[] = $parsingDispatcher->parse(
                $rawLocationData,
                $rawLocationData['_media-type']
            );
        }
        return $locations;
    }
}
