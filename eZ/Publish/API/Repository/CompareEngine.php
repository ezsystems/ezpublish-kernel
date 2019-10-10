<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\DiffValue;
use eZ\Publish\SPI\Compare\Field;

interface CompareEngine
{
    public function compareFieldsData(Field $fieldA, Field $fieldB): DiffValue;
}
