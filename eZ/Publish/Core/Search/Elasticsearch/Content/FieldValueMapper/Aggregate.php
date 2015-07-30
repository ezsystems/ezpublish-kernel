<?php

/**
 * File containing the Aggregate document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Maps raw document field values to something Elasticsearch can index.
 */
class Aggregate extends FieldValueMapper
{
    /**
     * Array of available mappers.
     *
     * @var \eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper[]
     */
    protected $mappers = array();

    /**
     * Construct from optional mapper array.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper[] $mappers
     */
    public function __construct(array $mappers = array())
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * Adds mapper.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper $mapper
     */
    public function addMapper(FieldValueMapper $mapper)
    {
        $this->mappers[] = $mapper;
    }

    /**
     * Check if field can be mapped.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return bool
     */
    public function canMap(Field $field)
    {
        return true;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($field)) {
                return $mapper->map($field);
            }
        }

        throw new NotImplementedException(
            'No mapper available for: ' . get_class($field->type)
        );
    }
}
