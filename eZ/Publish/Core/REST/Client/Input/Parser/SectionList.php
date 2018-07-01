<?php

/**
 * File containing the SectionList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for SectionList.
 */
class SectionList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $sections = array();
        foreach ($data['Section'] as $rawSectionData) {
            $sections[] = $parsingDispatcher->parse(
                $rawSectionData,
                $rawSectionData['_media-type']
            );
        }

        return $sections;
    }
}
