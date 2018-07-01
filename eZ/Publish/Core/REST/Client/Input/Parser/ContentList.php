<?php

/**
 * File containing the ContentList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for ContentList.
 */
class ContentList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contents = array();
        foreach ($data['Content'] as $rawContentData) {
            $contents[] = $parsingDispatcher->parse(
                $rawContentData,
                $rawContentData['_media-type']
            );
        }

        return $contents;
    }
}
