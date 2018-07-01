<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field as FieldSortClause;

class Field extends BaseParser
{
    /**
     * Parse input structure for Field sort clause.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!isset($data['Field'])) {
            throw new Exceptions\Parser("The <Field> sort clause doesn't exist in the input structure");
        }

        if (!is_array($data['Field'])) {
            throw new Exceptions\Parser('The <Field> sort clause has missing arguments: contentTypeIdentifier, fieldDefinitionIdentifier');
        }

        $data['Field'] = $this->normalizeData($data['Field']);

        $direction = isset($data['Field']['direction']) ? $data['Field']['direction'] : null;

        if (!in_array($direction, [Query::SORT_ASC, Query::SORT_DESC])) {
            throw new Exceptions\Parser('Invalid direction format in <Field> sort clause');
        }

        if (isset($data['Field']['identifier'])) {
            if (false === strpos($data['Field']['identifier'], '/')) {
                throw new Exceptions\Parser('<Field> sort clause parameter "identifier" value has to be in "contentTypeIdentifier/fieldDefinitionIdentifier" format');
            }

            list($contentTypeIdentifier, $fieldDefinitionIdentifier) = explode('/', $data['Field']['identifier'], 2);
        } else {
            if (!isset($data['Field']['contentTypeIdentifier'])) {
                throw new Exceptions\Parser('<Field> sort clause have missing parameter "contentTypeIdentifier"');
            }
            if (!isset($data['Field']['fieldDefinitionIdentifier'])) {
                throw new Exceptions\Parser('<Field> sort clause have missing parameter "fieldDefinitionIdentifier"');
            }

            $contentTypeIdentifier = $data['Field']['contentTypeIdentifier'];
            $fieldDefinitionIdentifier = $data['Field']['fieldDefinitionIdentifier'];
        }

        return new FieldSortClause($contentTypeIdentifier, $fieldDefinitionIdentifier, $direction);
    }

    /**
     * Normalize passed Field Sort Clause data by making both xml and json parameters to have same names (by dropping
     * xml "_" prefix and changing "#text" xml attribute to "direction").
     *
     * @param array $data
     *
     * @return array
     */
    private function normalizeData($data)
    {
        $normalizedData = [];

        foreach ($data as $key => $value) {
            if ('#text' === $key) {
                $key = 'direction';
            }

            $normalizedData[trim($key, '_')] = $value;
        }

        return $normalizedData;
    }
}
