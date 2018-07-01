<?php

/**
 * File containing the RoleList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for RoleList.
 */
class RoleList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $roles = array();
        foreach ($data['Role'] as $rawRoleData) {
            $roles[] = $parsingDispatcher->parse(
                $rawRoleData,
                $rawRoleData['_media-type']
            );
        }

        return $roles;
    }
}
