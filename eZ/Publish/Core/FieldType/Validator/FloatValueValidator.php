<?php

/**
 * File containing the FloatValueValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator to validate ranges in float values.
 *
 * Note that this validator can be limited by limitation on precision when
 * dealing with floating point numbers, and conversions.
 *
 * @property float $minFloatValue Minimum value for float.
 * @property float $maxFloatValue Maximum value for float.
 */
class FloatValueValidator extends Validator
{
    protected $constraints = [
        'minFloatValue' => null,
        'maxFloatValue' => null,
    ];

    protected $constraintsSchema = [
        'minFloatValue' => [
            'type' => 'float',
            'default' => null,
        ],
        'maxFloatValue' => [
            'type' => 'float',
            'default' => null,
        ],
    ];

    public function validateConstraints($constraints)
    {
        $validationErrors = [];

        foreach ($constraints as $name => $value) {
            switch ($name) {
                case 'minFloatValue':
                case 'maxFloatValue':
                    if ($value !== null && !is_numeric($value)) {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of numeric type",
                            null,
                            [
                                '%parameter%' => $name,
                            ]
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        [
                            '%parameter%' => $name,
                        ]
                    );
            }
        }

        return $validationErrors;
    }

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constant has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Float\Value $value
     *
     * @return bool
     */
    public function validate(BaseValue $value)
    {
        $isValid = true;

        if ($this->constraints['maxFloatValue'] !== null && $value->value > $this->constraints['maxFloatValue']) {
            $this->errors[] = new ValidationError(
                'The value can not be higher than %size%.',
                null,
                [
                    '%size%' => $this->constraints['maxFloatValue'],
                ]
            );
            $isValid = false;
        }

        if ($this->constraints['minFloatValue'] !== null && $value->value < $this->constraints['minFloatValue']) {
            $this->errors[] = new ValidationError(
                'The value can not be lower than %size%.',
                null,
                [
                    '%size%' => $this->constraints['minFloatValue'],
                ]
            );
            $isValid = false;
        }

        return $isValid;
    }
}
