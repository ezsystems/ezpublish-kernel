<?php

/**
 * File containing the PolicyList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for PolicyList.
 */
class PolicyList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $policies = array();

        if (array_key_exists('Policy', $data) && is_array($data['Policy'])) {
            foreach ($data['Policy'] as $rawPolicyData) {
                $policies[] = $parsingDispatcher->parse(
                    $rawPolicyData,
                    $rawPolicyData['_media-type']
                );
            }
        }

        return $policies;
    }
}
