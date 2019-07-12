<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType;

interface ValueSerializerInterface
{
    /**
     * Normalizes an object into a hash.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     * @param array $context
     *
     * @return array|null
     */
    public function normalize(Value $value, array $context = []): ?array;

    /**
     * Denormalize data into an object of the given class.
     *
     * @param array|null $data
     * @param string $valueClass
     * @param array $context
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function denormalize(?array $data, string $valueClass, array $context = []): Value;

    /**
     * Encode normalized data.
     *
     * @param array|null $data
     * @param array $context
     *
     * @return string|null
     */
    public function encode(?array $data, array $context = []): ?string;

    /**
     * Decodes a string into PHP data.
     *
     * @param string|null $data
     * @param array $context
     *
     * @return array|null
     */
    public function decode(?string $data, array $context = []): ?array;
}
