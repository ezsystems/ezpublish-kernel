<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Common aggregate mapper implementation.
 */
class Aggregate extends FieldValueMapper
{
    /**
     * Array of available mappers.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldValueMapper[]
     */
    protected $mappers = [];

    /**
     * Construct from optional mapper array.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * Adds mapper.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldValueMapper $mapper
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
     * Map field value to a proper search engine representation.
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
