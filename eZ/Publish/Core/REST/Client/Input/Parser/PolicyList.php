<?php
/**
 * File containing the Policy List parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for PolicyList
 */
class PolicyList extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     * @return ValueObject
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $policies = array();

        if ( array_key_exists( 'Policy', $data ) && is_array( $data['Policy'] ) )
        {
            foreach ( $data['Policy'] as $rawPolicyData )
            {
                $policies[] = $parsingDispatcher->parse(
                    $rawPolicyData,
                    $rawPolicyData['_media-type']
                );
            }
        }

        return $policies;
    }
}
