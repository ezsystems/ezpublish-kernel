<?php

/**
 * File containing the ContentTypeGroupId parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId as ContentTypeGroupIdCriterion;

/**
 * Parser for ContentTypeGroupId Criterion.
 */
class ContentTypeGroupId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('ContentTypeGroupIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ContentTypeGroupIdCriterion> format');
        }

        return new ContentTypeGroupIdCriterion($data['ContentTypeGroupIdCriterion']);
    }
}
