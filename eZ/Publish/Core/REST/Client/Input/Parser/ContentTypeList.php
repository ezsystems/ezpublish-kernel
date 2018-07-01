<?php

/**
 * File containing the ContentTypeList parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

/**
 * Parser for ContentTypeList.
 */
class ContentTypeList extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @todo Error handling
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[]
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $contentTypes = array();
        foreach ($data['ContentType'] as $rawContentTypeData) {
            $contentTypes[] = $parsingDispatcher->parse(
                $rawContentTypeData,
                $rawContentTypeData['_media-type']
            );
        }

        return $contentTypes;
    }
}
