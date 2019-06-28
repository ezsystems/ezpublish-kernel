<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs;

use eZ\Publish\Core\FieldType\Generic\Type;

final class GenericFieldType extends Type
{
    public function getFieldTypeIdentifier(): string
    {
        return 'field_type_identifier';
    }
}
