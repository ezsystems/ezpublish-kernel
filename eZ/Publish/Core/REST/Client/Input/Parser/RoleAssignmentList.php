<?php

/**
 * File containing the RoleAssignmentList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for RoleAssignmentList.
 */
class RoleAssignmentList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
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
