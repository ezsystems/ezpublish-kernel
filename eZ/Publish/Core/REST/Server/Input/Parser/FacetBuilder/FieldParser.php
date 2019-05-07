<?php

/**
 * File containing the facet builder Field parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\FacetBuilder;

use EzSystems\EzPlatformRestCommon\Input\BaseParser;
use EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher;
use EzSystems\EzPlatformRestCommon\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder;

/**
 * Parser for Field facet builder.
 */
class FieldParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRestCommon\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \EzSystems\EzPlatformRestCommon\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Field', $data)) {
            throw new Exceptions\Parser('Invalid <Field> format');
        }

        $sortType = [
            'COUNT_ASC' => FieldFacetBuilder::COUNT_ASC,
            'COUNT_DESC' => FieldFacetBuilder::COUNT_DESC,
            'TERM_ASC' => FieldFacetBuilder::TERM_ASC,
            'TERM_DESC' => FieldFacetBuilder::TERM_DESC,
        ];

        if (isset($data['Field']['sort'])) {
            $type = $data['Field']['sort'];

            if (!in_array($type, $sortType)) {
                throw new Exceptions\Parser('<Field> unknown sort type (supported: ' . implode(', ', array_keys($sortType)) . ')');
            }

            $data['Field']['sort'] = $sortType[$type];
        } else {
            throw new Exceptions\Parser('<Field> sort type missing (supported: ' . implode(', ', array_keys($sortType)) . ')');
        }

        return new FieldFacetBuilder($data['Field']);
    }
}
