<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\Generic\Stubs;

use eZ\Publish\Core\FieldType\Generic\Type as BaseType;

final class Type extends BaseType
{
    public function getFieldTypeIdentifier(): string
    {
        return 'generic';
    }
}
