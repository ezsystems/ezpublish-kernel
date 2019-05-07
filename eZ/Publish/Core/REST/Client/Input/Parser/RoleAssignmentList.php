<?php

/**
 * File containing the RoleAssignmentList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;

/**
 * Parser for RoleAssignmentList.
 */
class RoleAssignmentList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $roleAssignments = array();
        foreach ($data['RoleAssignment'] as $rawRoleAssignmentData) {
            $roleAssignments[] = $parsingDispatcher->parse(
                $rawRoleAssignmentData,
                $rawRoleAssignmentData['_media-type']
            );
        }

        return $roleAssignments;
    }
}
