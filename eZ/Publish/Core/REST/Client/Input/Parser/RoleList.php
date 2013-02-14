<?php
/**
 * File containing the RoleList parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for RoleList
 */
class RoleList extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
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
