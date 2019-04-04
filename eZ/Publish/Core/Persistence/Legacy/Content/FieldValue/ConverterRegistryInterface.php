<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue;

interface ConverterRegistryInterface
{
    /**
     * Register a $converter for $typeName.
     *
     * @param string $typeName
     * @param mixed $converter Callable or converter instance
     */
    public function register($typeName, $converter);

    /**
     * Returns converter for $typeName.
     *
     * @param string $typeName
     *
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @throws \RuntimeException When type is neither Converter instance or callable factory
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    public function getConverter($typeName);
}
