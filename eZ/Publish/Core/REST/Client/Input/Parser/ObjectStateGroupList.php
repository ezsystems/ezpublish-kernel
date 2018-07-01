<?php

/**
 * File containing the ObjectStateGroupList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for ObjectStateGroupList.
 */
class ObjectStateGroupList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $groups = array();
        foreach ($data['ObjectStateGroup'] as $rawGroupData) {
            $groups[] = $parsingDispatcher->parse(
                $rawGroupData,
                $rawGroupData['_media-type']
            );
        }

        return $groups;
    }
}
