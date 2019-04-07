<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\SPI\FieldType\Value as BaseValue;

interface Value extends BaseValue
{
    public function __toString();
}
