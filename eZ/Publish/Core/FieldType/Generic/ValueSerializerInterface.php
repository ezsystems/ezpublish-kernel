<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

interface ValueSerializerInterface
{
    public function normalize(Value $value, array $context = []): ?array;

    public function denormalize(?array $data, string $valueClass, array $context = []): Value;

    public function encode($data, array $context = []): ?string;

    public function decode($data, array $context = []): ?array;
}
