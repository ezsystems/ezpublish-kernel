<?php

/**
 * File containing the IntegerValueValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validate ranges of integer value.
 *
 * @property int $minIntegerValue The minimum allowed integer value.
 * @property int $maxIntegerValue The maximum allowed integer value.
 */
class IntegerValueValidator extends Validator
{
    protected $constraints = [
        'minIntegerValue' => null,
        'maxIntegerValue' => null,
    ];

    protected $constraintsSchema = [
        'minIntegerValue' => [
            'type' => 'int',
            'default' => 0,
        ],
        'maxIntegerValue' => [
            'type' => 'int',
            'default' => null,
        ],
    ];

    public function validateConstraints($constraints)
    {
        $validationErrors = [];
        foreach ($constraints as $name => $value) {
            switch ($name) {
                case 'minIntegerValue':
                case 'maxIntegerValue':
                    if ($value === false) {
                        @trigger_error(
                            "The IntegerValueValidator constraint value 'false' is deprecated, and will be removed in 7.0. Use 'null' instead.",
                            E_USER_DEPRECATED
                        );
                        $value = null;
                    }
                    if ($value !== null && !is_int($value)) {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of integer type",
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
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Integer\Value $value
     *
     * @return bool
     */
    public function validate(BaseValue $value)
    {
        $isValid = true;

        if ($this->constraints['maxIntegerValue'] !== null && $value->value > $this->constraints['maxIntegerValue']) {
            $this->errors[] = new ValidationError(
                'The value can not be higher than %size%.',
                null,
                [
                    '%size%' => $this->constraints['maxIntegerValue'],
                ]
            );
            $isValid = false;
        }

        if ($this->constraints['minIntegerValue'] !== null && $value->value < $this->constraints['minIntegerValue']) {
            $this->errors[] = new ValidationError(
                'The value can not be lower than %size%.',
                null,
                [
                    '%size%' => $this->constraints['minIntegerValue'],
                ]
            );
            $isValid = false;
        }

        return $isValid;
    }
}
