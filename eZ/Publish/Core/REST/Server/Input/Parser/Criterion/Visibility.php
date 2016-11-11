<?php

/**
 * File containing the Visibility Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility as VisibilityCriterion;

/**
 * Parser for Visibility Criterion.
 */
class Visibility extends BaseParser
{
    /**
     * Parses input structure to a Visibility Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('VisibilityCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <VisibilityCriterion> format');
        }

        if ($data['VisibilityCriterion'] != VisibilityCriterion::VISIBLE && $data['VisibilityCriterion'] != VisibilityCriterion::HIDDEN) {
            throw new Exceptions\Parser('Invalid <VisibilityCriterion> format');
        }

        return new VisibilityCriterion((int)$data['VisibilityCriterion']);
    }
}
