<?php

/**
 * File containing the LocationList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;

/**
 * Parser for LocationList.
 */
class LocationList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $locations = array();
        foreach ($data['Location'] as $rawLocationData) {
            $locations[] = $parsingDispatcher->parse(
                $rawLocationData,
                $rawLocationData['_media-type']
            );
        }

        return $locations;
    }
}
