<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Validator;

use eZ\Publish\Core\FieldType\ValidationError;

/**
 * Internal service to user password validation against specified constraints.
 *
 * @internal Meant for internal use by Repository.
 */
class UserPasswordValidator
{
    private const AT_LEAST_ONE_LOWER_CASE_CHARACTER_REGEX = '/\p{Ll}/u';
    private const AT_LEAST_ONE_UPPER_CASE_CHARACTER_REGEX = '/\p{Lu}/u';
    private const AT_LEAST_ONE_NUMERIC_CHARACTER_REGEX = '/\pN/u';
    private const AT_LEAST_ONE_NON_ALPHANUMERIC_CHARACTER_REGEX = '/[^\p{Ll}\p{Lu}\pL\pN]/u';

    /** @var array */
    private $constraints;

    /**
     * @param array $constraints
     */
    public function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }

    /**
     * Validates given $password.
     *
     * @param string $password
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (!$this->isLongEnough($password)) {
            $errors[] = $this->createValidationError('User password must be at least %length% characters long', [
                '%length%' => $this->constraints['minLength'],
            ]);
        }

        if (!$this->containsAtLeastOneLowerCaseCharacter($password)) {
            $errors[] = $this->createValidationError('User password must include at least one lower case letter');
        }

        if (!$this->containsAtLeastOneUpperCaseCharacter($password)) {
            $errors[] = $this->createValidationError('User password must include at least one upper case letter');
        }

        if (!$this->containsAtLeastOneNumericCharacter($password)) {
            $errors[] = $this->createValidationError('User password must include at least one number');
        }

        if (!$this->containsAtLeastOneNonAlphanumericCharacter($password)) {
            $errors[] = $this->createValidationError('User password must include at least one special character');
        }

        return $errors;
    }

    /**
     * Checks if given $password satisfies length requirements.
     *
     * @param string $password
     *
     * @return bool
     */
    private function isLongEnough(string $password): bool
    {
        if ((int) $this->constraints['minLength'] > 0) {
            return mb_strlen($password) >= (int) $this->constraints['minLength'];
        }

        return true;
    }

    /**
     * Checks if given $password contains at least one lower case character (if rule is applicable).
     *
     * @param string $password
     *
     * @return bool
     */
    private function containsAtLeastOneLowerCaseCharacter(string $password): bool
    {
        if ($this->constraints['requireAtLeastOneLowerCaseCharacter']) {
            return (bool)preg_match(self::AT_LEAST_ONE_LOWER_CASE_CHARACTER_REGEX, $password);
        }

        return true;
    }

    /**
     * Checks if given $password contains at least one upper case character (if rule is applicable).
     *
     * @param string $password
     *
     * @return bool
     */
    private function containsAtLeastOneUpperCaseCharacter(string $password): bool
    {
        if ($this->constraints['requireAtLeastOneUpperCaseCharacter']) {
            return (bool)preg_match(self::AT_LEAST_ONE_UPPER_CASE_CHARACTER_REGEX, $password);
        }

        return true;
    }

    /**
     * Checks if given $password contains at least one numeric character (if rule is applicable).
     *
     * @param string $password
     *
     * @return bool
     */
    private function containsAtLeastOneNumericCharacter(string $password): bool
    {
        if ($this->constraints['requireAtLeastOneNumericCharacter']) {
            return (bool)preg_match(self::AT_LEAST_ONE_NUMERIC_CHARACTER_REGEX, $password);
        }

        return true;
    }

    /**
     * Checks if given $password contains at least one non alphanumeric character (if rule is applicable).
     *
     * @param string $password
     *
     * @return bool
     */
    private function containsAtLeastOneNonAlphanumericCharacter(string $password): bool
    {
        if ($this->constraints['requireAtLeastOneNonAlphanumericCharacter']) {
            return (bool)preg_match(self::AT_LEAST_ONE_NON_ALPHANUMERIC_CHARACTER_REGEX, $password);
        }

        return true;
    }

    /**
     * Creates a validation error with given messages and placeholders.
     *
     * @param string $message
     * @param array $values
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError
     */
    private function createValidationError(string $message, array $values = []): ValidationError
    {
        return new ValidationError($message, null, $values, 'password');
    }
}
