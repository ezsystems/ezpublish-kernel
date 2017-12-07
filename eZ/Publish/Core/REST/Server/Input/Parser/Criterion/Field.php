<?php

/**
 * File containing the Field Criterion parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field as FieldCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for Field Criterion.
 */
class Field extends BaseParser
{
    const OPERATORS = [
        'IN' => Operator::IN,
        'EQ' => Operator::EQ,
        'GT' => Operator::GT,
        'GTE' => Operator::GTE,
        'LT' => Operator::LT,
        'LTE' => Operator::LTE,
        'LIKE' => Operator::LIKE,
        'BETWEEN' => Operator::BETWEEN,
        'CONTAINS' => Operator::CONTAINS,
    ];

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\Parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('Field', $data)) {
            throw new Exceptions\Parser('Invalid <Field> format');
        }

        $fieldData = $data['Field'];
        if (empty($fieldData['name']) || empty($fieldData['operator']) || empty($fieldData['value'])) {
            throw new Exceptions\Parser('<Field> format expects name, operator and value keys');
        }

        $operator = $this->getOperator($fieldData['operator']);

        $values = is_array($fieldData['value']) ? $fieldData['value'] : [$fieldData['value']];
        $criteria = [];
        foreach ($values as $value) {
            $criteria[] = new FieldCriterion(
                $fieldData['name'],
                $operator,
                $value
            );
        }

        return new LogicalAnd($criteria);
    }

    /**
     * Get operator for the given literal name.
     *
     * For the full list of supported operators:
     * @see \eZ\Publish\Core\REST\Server\Input\Parser\Criterion\Field::OPERATORS
     *
     * @param string $operatorName operator literal operator name
     *
     * @return string
     */
    private function getOperator($operatorName)
    {
        $operatorName = strtoupper($operatorName);
        if (!isset(self::OPERATORS[$operatorName])) {
            throw new Exceptions\Parser(
                sprintf(
                    'Unexpected Field operator, expected one of the following: %s',
                    implode(', ', array_keys(self::OPERATORS))
                )
            );
        }

        return self::OPERATORS[$operatorName];
    }
}
