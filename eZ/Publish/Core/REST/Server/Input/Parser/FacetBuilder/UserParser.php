<?php

/**
 * File containing the facet builder User parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder;

/**
 * Parser for User facet builder.
 */
class UserParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRestCommon\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('User', $data)) {
            throw new Exceptions\Parser('Invalid <User> format');
        }

        $selectType = [
            'OWNER' => UserFacetBuilder::OWNER,
            'GROUP' => UserFacetBuilder::GROUP,
            'MODIFIER' => UserFacetBuilder::MODIFIER,
        ];

        if (isset($data['User']['select'])) {
            $type = $data['User']['select'];

            if (!isset($selectType[$type])) {
                throw new Exceptions\Parser('<User> unknown type (supported: ' . implode(', ', array_keys($selectType)) . ')');
            }

            $data['User']['type'] = $selectType[$type];

            unset($data['User']['select']);
        }

        return new UserFacetBuilder($data['User']);
    }
}
