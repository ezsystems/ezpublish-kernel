<?php
/**
 * File containing the Role List parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Input\Parser;

use eZ\Publish\API\REST\Common\Input\Parser;
use eZ\Publish\API\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for RoleList
 */
class RoleList extends Parser
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
        $roles = array();
        foreach ( $data['Role'] as $rawRoleData )
        {
            $roles[] = $parsingDispatcher->parse(
                $rawRoleData,
                $rawRoleData['_media-type']
            );
        }
        return $roles;
    }
}
