<?php

/**
 * File containing the ViewInput Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\Core\REST\Common\Exceptions\Parser;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata as DateMetadataCriterion;

/**
 * Parser for ViewInput Criterion.
 */
class DateMetadata extends BaseParser
{
    /** @var array */
    private $availableOperators = [
        '=',
        '>',
        '>=',
        '<',
        '<=',
        'in',
        'between',
    ];

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('DateMetadataCriterion', $data)) {
            throw new Parser('Invalid <DateMetadataCriterion> format');
        }

        if (!isset($data['DateMetadataCriterion']['Target'])) {
            throw new Parser('Invalid <Target> format');
        }
        $target = $data['DateMetadataCriterion']['Target'];

        if (!isset($data['DateMetadataCriterion']['Operator'])) {
            $data['DateMetadataCriterion']['Operator'] = '=';
        } elseif (!in_array($data['DateMetadataCriterion']['Operator'], $this->availableOperators)) {
            throw new Parser('Invalid <Operator> format');
        }

        if (
            !isset($data['DateMetadataCriterion']['Value']) ||
            !in_array(gettype($data['DateMetadataCriterion']['Value']), ['integer', 'string', 'array'])
        ) {
            throw new Parser('Invalid <Value> format');
        }
        $value = $data['DateMetadataCriterion']['Value'];

        return new DateMetadataCriterion($target, $data['DateMetadataCriterion']['Operator'], $value);
    }
}
