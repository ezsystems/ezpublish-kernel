<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\ValidationError;

interface TargetContentValidatorInterface
{
    /**
     * @param mixed $value
     */
    public function validate($value): ?ValidationError;
}
