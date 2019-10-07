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
final class PasswordHashService implements PasswordHashServiceInterface
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

    public function isValidPassword(string $plainPassword, string $passwordHash, ?int $hashType = null): bool
    {
        if ($hashType === User::PASSWORD_HASH_BCRYPT || $hashType === User::PASSWORD_HASH_PHP_DEFAULT) {
            // In case of bcrypt let php's password functionality do it's magic
            return password_verify($plainPassword, $passwordHash);
        }

        // Randomize login time to protect against timing attacks
        usleep(random_int(0, 30000));

        return $passwordHash === $this->createPasswordHash($plainPassword, $hashType);
    }
}
