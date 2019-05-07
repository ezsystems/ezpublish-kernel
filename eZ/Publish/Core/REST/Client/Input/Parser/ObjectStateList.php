<?php

/**
 * File containing the ObjectStateList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;

/**
 * Parser for ObjectStateList.
 */
class ObjectStateList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $states = array();
        foreach ($data['ObjectState'] as $rawStateData) {
            $states[] = $parsingDispatcher->parse(
                $rawStateData,
                $rawStateData['_media-type']
            );
        }

        return $states;
    }
}
