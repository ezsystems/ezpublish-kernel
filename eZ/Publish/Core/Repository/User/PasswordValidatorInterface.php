<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\User;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * @internal
 */
interface PasswordValidatorInterface
{
    /**
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validatePassword(string $password, FieldDefinition $userFieldDefinition): array;
}
