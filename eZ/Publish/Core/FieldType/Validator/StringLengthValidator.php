<?php

/**
 * File containing the StringLengthValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;

/**
 * Validator for checking min. and max. length of strings.
 *
 * @property int $maxStringLength The maximum allowed length of the string.
 * @property int $minStringLength The minimum allowed length of the string.
 */
class StringLengthValidator extends Validator
{
    protected $constraints = array(
        'maxStringLength' => false,
        'minStringLength' => false,
    );

    protected $constraintsSchema = array(
        'minStringLength' => array(
            'type' => 'int',
            'default' => 0,
        ),
        'maxStringLength' => array(
            'type' => 'int',
            'default' => null,
        ),
    );

    public function validateConstraints($constraints)
    {
        $validationErrors = array();

        foreach (array_keys($constraints) as $constraint) {
            if (!in_array($constraint, array('minStringLength', 'maxStringLength', 'defaultValue'))) {
                $validationErrors[] = new ValidationError(
                    "Validator parameter '%parameter%' is unknown",
                    null,
                    array(
                        'parameter' => $constraint,
                    )
                );
            }
        }

        $minStringLength = isset($constraints['minStringLength']) ? $constraints['minStringLength'] : null;
        $maxStringLength = isset($constraints['maxStringLength']) ? $constraints['maxStringLength'] : null;
        $defaultValue = isset($constraints['defaultValue']) ? $constraints['defaultValue'] : null;

        if ($defaultValue instanceof TextLineValue) {
            $defaultValue = $defaultValue->text;
        }

        if ($minStringLength !== false && !is_integer($minStringLength) && !(null === $minStringLength)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' value must be of integer type",
                null,
                array(
                    'parameter' => 'minStringLength',
                )
            );
        } elseif ($minStringLength < 0) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' value can't be negative",
                null,
                array(
                    'parameter' => 'minStringLength',
                )
            );
        }

        if ($maxStringLength !== false && !is_integer($maxStringLength) && !(null === $maxStringLength)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' value must be of integer type",
                null,
                array(
                    'parameter' => 'maxStringLength',
                )
            );
        } elseif ($maxStringLength < 0) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' value can't be negative",
                null,
                array(
                    'parameter' => 'maxStringLength',
                )
            );
        } elseif (!$this->validateConstraintsOrder($constraints)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter 'maxStringLength' can't be shorter than validator parameter 'minStringLength' value",
                null
            );
        } elseif (!(null === $defaultValue) && (strlen($defaultValue) < $minStringLength)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' length can't be shorter than minimum string length value",
                null,
                array(
                    'parameter' => 'defaultValue',
                )
            );
        } elseif (!(null === $defaultValue) && (strlen($defaultValue) > $maxStringLength)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter '%parameter%' length can't be greater than maximum string length value",
                null,
                array(
                    'parameter' => 'defaultValue',
                )
            );
        }

        return $validationErrors;
    }

    /**
     * Check if max string length is greater or equal than min string length in
     * case both are set. Returns also true in case one of them is not set.
     *
     * @param $constraints
     *
     * @return bool
     */
    protected function validateConstraintsOrder($constraints)
    {
        return !isset($constraints['minStringLength']) ||
            !isset($constraints['maxStringLength']) ||
            ($constraints['minStringLength'] <= $constraints['maxStringLength']);
    }

    /**
     * Checks if the string $value is in desired range.
     *
     * The range is determined by $maxStringLength and $minStringLength.
     *
     * @param \eZ\Publish\Core\FieldType\TextLine\Value $value
     *
     * @return bool
     */
    public function validate(BaseValue $value)
    {
        $isValid = true;

        if ($this->constraints['maxStringLength'] !== false &&
            $this->constraints['maxStringLength'] !== 0 &&
            strlen($value->text) > $this->constraints['maxStringLength']) {
            $this->errors[] = new ValidationError(
                'The string can not exceed %size% character.',
                'The string can not exceed %size% characters.',
                array(
                    'size' => $this->constraints['maxStringLength'],
                )
            );
            $isValid = false;
        }
        if ($this->constraints['minStringLength'] !== false &&
            $this->constraints['minStringLength'] !== 0 &&
            strlen($value->text) < $this->constraints['minStringLength']) {
            $this->errors[] = new ValidationError(
                'The string can not be shorter than %size% character.',
                'The string can not be shorter than %size% characters.',
                array(
                    'size' => $this->constraints['minStringLength'],
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
