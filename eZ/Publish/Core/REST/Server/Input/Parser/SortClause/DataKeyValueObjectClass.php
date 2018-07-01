<?php

namespace eZ\Publish\Core\REST\Server\Input\Parser\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;

class DataKeyValueObjectClass extends BaseParser
{
    /**
     * Data key, corresponding to the $valueObjectClass class.
     * Example: 'DatePublished'.
     * @var string
     */
    protected $dataKey;

    /**
     * Value object class, corresponding to the $dataKey.
     * Example: 'eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished'.
     * @var string
     */
    protected $valueObjectClass;

    /**
     * DataKeyValueObjectClass constructor.
     *
     * @param string $dataKey
     * @param string $valueObjectClass
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
        if (!class_exists($this->valueObjectClass)) {
            throw new Exceptions\Parser("Value object class <{$this->valueObjectClass}> is not defined");
        }

        if (!array_key_exists($this->dataKey, $data)) {
            throw new Exceptions\Parser("The <{$this->dataKey}> sort clause doesn't exist in the input structure");
        }

        $direction = $data[$this->dataKey];

        if (!in_array($direction, [Query::SORT_ASC, Query::SORT_DESC])) {
            throw new Exceptions\Parser("Invalid direction format in <{$this->dataKey}> sort clause");
        }

        return new $this->valueObjectClass($direction);
    }
}
