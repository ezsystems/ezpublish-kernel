<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use DateTime;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents a user value.
 *
 * @property-read string $login
 * @property-read string $email
 * @property-read string $passwordHash
 * @property-read string $hashAlgorithm Hash algorithm used to hash the password
 * @property-read \DateTimeInterface|null $passwordUpdatedAt
 * @property-read bool $enabled User can not login if false
 * @property-read int $maxLogin Max number of time user is allowed to login
 */
abstract class User extends Content implements UserReference
{
    public const SUPPORTED_PASSWORD_HASHES = [
        self::PASSWORD_HASH_BCRYPT,
        self::PASSWORD_HASH_PHP_DEFAULT,
    ];

    /** @var int Passwords in bcrypt */
    const PASSWORD_HASH_BCRYPT = 6;

    /** @var int Passwords hashed by PHPs default algorithm, which may change over time */
    const PASSWORD_HASH_PHP_DEFAULT = 7;

    /** @var int Default password hash, used when none is specified, may change over time */
    const DEFAULT_PASSWORD_HASH = self::PASSWORD_HASH_PHP_DEFAULT;

    /**
     * User login.
     *
     * @var string
     */
    protected $login;

    /**
     * User E-Mail address.
     *
     * @var string
     */
    protected $email;

    /**
     * User password hash.
     *
     * @var string
     */
    protected $passwordHash;

    /**
     * Datetime of last password update.
     *
     * @var \DateTimeInterface|null
     */
    protected $passwordUpdatedAt;

    /**
     * Hash algorithm used to hash the password.
     *
     * @var int
     */
    protected $hashAlgorithm;

    /**
     * Flag to signal if user is enabled or not.
     *
     * User can not login if false
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Max number of time user is allowed to login.
     *
     * @todo: Not used in kernel, should probably be a number of login allowed before changing password.
     *        But new users gets 0 before they activate, admin has 10, and anonymous has 1000 in clean data.
     *
     * @var int
     */
    protected $maxLogin;

    /**
     * The User id of the User.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->id;
    }
}
