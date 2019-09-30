<?php

/**
 * File containing the User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User;

use DateTimeImmutable;
use DateTimeInterface;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Persistence\Cache\UserHandler;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * The User field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    public const PASSWORD_TTL_SETTING = 'PasswordTTL';
    public const PASSWORD_TTL_WARNING_SETTING = 'PasswordTTLWarning';

    /** @var \eZ\Publish\Core\Persistence\Cache\UserHandler */
    protected $userHandler;

    /** @var array */
    protected $settingsSchema = [
        self::PASSWORD_TTL_SETTING => [
            'type' => 'int',
            'default' => null,
        ],
        self::PASSWORD_TTL_WARNING_SETTING => [
            'type' => 'int',
            'default' => null,
        ],
    ];

    /** @var array */
    protected $validatorConfigurationSchema = [
        'PasswordValueValidator' => [
            'requireAtLeastOneUpperCaseCharacter' => [
                'type' => 'int',
                'default' => 1,
            ],
            'requireAtLeastOneLowerCaseCharacter' => [
                'type' => 'int',
                'default' => 1,
            ],
            'requireAtLeastOneNumericCharacter' => [
                'type' => 'int',
                'default' => 1,
            ],
            'requireAtLeastOneNonAlphanumericCharacter' => [
                'type' => 'int',
                'default' => null,
            ],
            'requireNewPassword' => [
                'type' => 'int',
                'default' => null,
            ],
            'minLength' => [
                'type' => 'int',
                'default' => 10,
            ],
        ],
    ];

    /**
     * @param \eZ\Publish\Core\Persistence\Cache\UserHandler $userHandler
     */
    public function __construct(UserHandler $userHandler)
    {
        $this->userHandler = $userHandler;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezuser';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string)$value->login;
    }

    /**
     * Indicates if the field definition of this type can appear only once in the same ContentType.
     *
     * @return bool
     */
    public function isSingular()
    {
        return true;
    }

    /**
     * Indicates if the field definition of this type can be added to a ContentType with Content instances.
     *
     * @return bool
     */
    public function onlyEmptyInstance()
    {
        return true;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\User\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\eZ\Publish\Core\FieldType\User\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\User\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = $this->fromHash($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        // Does nothing
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\User\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        if (isset($hash['passwordUpdatedAt']) && $hash['passwordUpdatedAt'] !== null) {
            $hash['passwordUpdatedAt'] = new DateTimeImmutable('@' . $hash['passwordUpdatedAt']);
        }

        return new Value($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        $hash = (array)$value;
        if ($hash['passwordUpdatedAt'] instanceof DateTimeInterface) {
            $hash['passwordUpdatedAt'] = $hash['passwordUpdatedAt']->getTimestamp();
        }

        return $hash;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * In this method the field type puts the data which is stored in the field of content in the repository
     * into the property FieldValue::data. The format of $data is a primitive, an array (map) or an object, which
     * is then canonically converted to e.g. json/xml structures by future storage engines without
     * further conversions. For mapping the $data to the legacy database an appropriate Converter
     * (implementing eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter) has implemented for the field
     * type. Note: $data should only hold data which is actually stored in the field. It must not
     * hold data which is stored externally.
     *
     * The $externalData property in the FieldValue is used for storing data externally by the
     * FieldStorage interface method storeFieldData.
     *
     * The FieldValuer::sortKey is build by the field type for using by sort operations.
     *
     * @see \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => null,
            ]
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\User\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return $this->acceptValue($fieldValue->externalData);
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\User\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = [];

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        if (!$fieldValue->hasStoredLogin) {
            try {
                $login = $fieldValue->login;
                $this->userHandler->loadByLogin($login);

                // If you want to change this ValidationError message, please remember to change it also in Repository Forms in lib/Validator/Constraints/FieldValueValidatorMessages class
                $errors[] = new ValidationError(
                    "The user login '%login%' is used by another user. You must enter a unique login.",
                    null,
                    [
                        '%login%' => $login,
                    ],
                    'username'
                );
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = [];

        foreach ((array)$validatorConfiguration as $validatorIdentifier => $constraints) {
            if ($validatorIdentifier !== 'PasswordValueValidator') {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    [
                        'validator' => $validatorIdentifier,
                    ],
                    "[$validatorIdentifier]"
                );
            }
        }

        return $validationErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = [];

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    [
                        '%setting%' => $name,
                    ],
                    "[$name]"
                );

                continue;
            }

            $error = null;
            switch ($name) {
                case self::PASSWORD_TTL_SETTING:
                    $error = $this->validatePasswordTTLSetting($name, $value);
                    break;
                case self::PASSWORD_TTL_WARNING_SETTING:
                    $error = $this->validatePasswordTTLWarningSetting($name, $value, $fieldSettings);
                    break;
            }

            if ($error !== null) {
                $validationErrors[] = $error;
            }
        }

        return $validationErrors;
    }

    private function validatePasswordTTLSetting(string $name, $value): ?ValidationError
    {
        if ($value !== null && !is_int($value)) {
            return new ValidationError(
                "Setting '%setting%' value must be of integer type",
                null,
                [
                    '%setting%' => $name,
                ],
                "[$name]"
            );
        }

        return null;
    }

    private function validatePasswordTTLWarningSetting(string $name, $value, $fieldSettings): ?ValidationError
    {
        if ($value !== null) {
            if (!is_int($value)) {
                return new ValidationError(
                    "Setting '%setting%' value must be of integer type",
                    null,
                    [
                        '%setting%' => $name,
                    ],
                    "[$name]"
                );
            }

            if ($value > 0) {
                $passwordTTL = $fieldSettings[self::PASSWORD_TTL_SETTING] ?? null;
                if ($value >= (int)$passwordTTL) {
                    return new ValidationError(
                        'Password expiration warning value should be lower then password expiration value',
                        null,
                        [],
                        "[$name]"
                    );
                }
            }
        }

        return null;
    }
}
