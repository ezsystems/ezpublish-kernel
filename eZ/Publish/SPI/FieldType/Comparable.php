<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType;

interface Comparable
{
    public function valuesEqual(Value $value1, Value $value2): bool;
}
