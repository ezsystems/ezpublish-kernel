<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @internal
 */
final class PasswordHashGenerator implements PasswordHashGeneratorInterface
{
    /** @var int */
    private $hashType;

    public function __construct(int $hashType = User::DEFAULT_PASSWORD_HASH)
    {
        $this->hashType = $hashType;
    }

    public function getDefaultHashType(): int
    {
        return $this->hashType;
    }

    public function createPasswordHash(string $password, ?int $hashType = null): string
    {
        $hashType = $hashType ?? $this->hashType;

        switch ($hashType) {
            case User::PASSWORD_HASH_BCRYPT:
                return password_hash($password, PASSWORD_BCRYPT);

            case User::PASSWORD_HASH_PHP_DEFAULT:
                return password_hash($password, PASSWORD_DEFAULT);

            default:
                throw new InvalidArgumentException('hashType', "Password hash type '$hashType' is not recognized");
        }
    }
}
