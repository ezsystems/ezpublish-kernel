<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata as DateMetadataCriterion;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for ViewInput Criterion.
 */
class DateMetadata extends BaseParser
{
    private const OPERATORS = [
        'IN' => Operator::IN,
        'EQ' => Operator::EQ,
        'GT' => Operator::GT,
        'GTE' => Operator::GTE,
        'LT' => Operator::LT,
        'LTE' => Operator::LTE,
        'BETWEEN' => Operator::BETWEEN,
    ];

    private const TARGETS = [
        DateMetadataCriterion::MODIFIED,
        DateMetadataCriterion::CREATED,
    ];

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): DateMetadataCriterion
    {
        if (!isset($data['DateMetadataCriterion'])) {
            throw new Exceptions\Parser('Invalid <DateMetaDataCriterion> format');
        }

        $dateMetadata = $data['DateMetadataCriterion'];

        if (!isset($dateMetadata['Target'])) {
            throw new Exceptions\Parser('Invalid <Target> format');
        }

        $target = strtolower($dateMetadata['Target']);

        if (!in_array($target, self::TARGETS, true)) {
            throw new Exceptions\Parser('Invalid <Target> format');
        }

        if (!isset($dateMetadata['Value'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        if (!in_array(gettype($dateMetadata['Value']), ['integer', 'array'], true)) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        $value = $dateMetadata['Value'];

        if (!isset($dateMetadata['Operator'])) {
            throw new Exceptions\Parser('Invalid <Operator> format');
        }

        $operator = $this->getOperator($dateMetadata['Operator']);

        return new DateMetadataCriterion($target, $operator, $value);
    }

    /**
     * Get operator for the given literal name.
     *
     * For the full list of supported operators:
     *
     * @see \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\DateMetadata::OPERATORS
     */
    private function getOperator(string $operatorName): string
    {
        $operatorName = strtoupper($operatorName);
        if (!isset(self::OPERATORS[$operatorName])) {
            throw new Exceptions\Parser(
                sprintf(
                    'Unexpected DateMetadata operator. Expected one of: %s',
                    implode(', ', array_keys(self::OPERATORS))
                )
            );
        }

        return self::OPERATORS[$operatorName];
    }
}
