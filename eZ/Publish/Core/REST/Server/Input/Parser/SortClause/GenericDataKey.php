<?php

namespace eZ\Publish\Core\REST\Server\Input\Parser\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

class GenericDataKey extends BaseParser
{
    /** @var string $dataKey */
    protected $dataKey;

    /** @var string $valueObjectClass */
    protected $valueObjectClass;

    /**
     * GenericDataKey constructor.
     * 
     * @param $dataKey
     * @param $valueObjectClass
     */
    public function __construct($dataKey, $valueObjectClass)
    {
        $this->dataKey = $dataKey;
        $this->valueObjectClass = $valueObjectClass;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $direction = $data[$this->dataKey];

        if (!in_array($direction, [Query::SORT_ASC, Query::SORT_DESC])) {
            throw new Exceptions\Parser("Invalid direction format in <{$direction}> sort clause");
        }

        return new $this->valueObjectClass($direction);
    }
}
