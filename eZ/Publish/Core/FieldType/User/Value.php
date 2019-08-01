<?php

/**
 * File containing the User Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for User field type.
 */
class Value extends BaseValue
{
    /**
     * Has stored login.
     *
     * @var bool
     */
    public $hasStoredLogin;

    /**
     * Contentobject id.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Login.
     *
     * @var string
     */
    public $login;

    /**
     * Email.
     *
     * @var string
     */
    public $email;

    /**
     * Password hash.
     *
     * @var string
     */
    public $passwordHash;

    /**
     * Password hash type.
     *
     * @var mixed
     */
    public $passwordHashType;

    /**
     * @var \DateTimeImmutable|null
     */
    public $passwordUpdatedAt;

    /**
     * Is enabled.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Max login.
     *
     * @var int
     */
    public $maxLogin;

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->login;
    }
}
