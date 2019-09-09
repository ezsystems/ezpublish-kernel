<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\User;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Validator\UserPasswordValidator;

/**
 * @internal
 */
final class PasswordValidator implements PasswordValidatorInterface
{
    /**
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validatePassword(string $password, FieldDefinition $userFieldDefinition): array
    {
        $configuration = $userFieldDefinition->getValidatorConfiguration();
        if (!isset($configuration['PasswordValueValidator'])) {
            return [];
        }

        return (new UserPasswordValidator($configuration['PasswordValueValidator']))->validate($password);
    }
}
