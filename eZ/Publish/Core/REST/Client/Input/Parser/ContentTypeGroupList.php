<?php

/**
 * File containing the ContentTypeGroupList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for ContentTypeGroupList.
 */
class ContentTypeGroupList extends BaseParser
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
        $contentTypeGroups = array();
        foreach ($data['ContentTypeGroup'] as $rawContentTypeGroupData) {
            $contentTypeGroups[] = $parsingDispatcher->parse(
                $rawContentTypeGroupData,
                $rawContentTypeGroupData['_media-type']
            );
        }

        return $contentTypeGroups;
    }
}
