<?php

/**
 * File containing the StringLengthValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator for checking min. and max. length of strings.
 *
 * @property int $maxStringLength The maximum allowed length of the string.
 * @property int $minStringLength The minimum allowed length of the string.
 */
class StringLengthValidator extends Validator
{
    protected $constraints = [
        'maxStringLength' => false,
        'minStringLength' => false,
    ];

    protected $constraintsSchema = [
        'minStringLength' => [
            'type' => 'int',
            'default' => 0,
        ],
        'maxStringLength' => [
            'type' => 'int',
            'default' => null,
        ],
    ];

    public function validateConstraints($constraints)
    {
        $validationErrors = [];
        foreach ($constraints as $name => $value) {
            switch ($name) {
                case 'minStringLength':
                case 'maxStringLength':
                    if ($value !== false && !is_int($value) && !(null === $value)) {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of integer type",
                            null,
                            [
                                '%parameter%' => $name,
                            ]
                        );
                    } elseif ($value < 0) {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value can't be negative",
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

        // if no errors above, check if minStringLength is shorter or equal than maxStringLength
        if (empty($validationErrors) && !$this->validateConstraintsOrder($constraints)) {
            $validationErrors[] = new ValidationError(
                "Validator parameter 'maxStringLength' can't be shorter than validator parameter 'minStringLength' value"
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
            mb_strlen($value->text) > $this->constraints['maxStringLength']) {
            $this->errors[] = new ValidationError(
                'The string can not exceed %size% character.',
                'The string can not exceed %size% characters.',
                [
                    '%size%' => $this->constraints['maxStringLength'],
                ]
            );
            $isValid = false;
        }
        if ($this->constraints['minStringLength'] !== false &&
            $this->constraints['minStringLength'] !== 0 &&
            mb_strlen($value->text) < $this->constraints['minStringLength']) {
            $this->errors[] = new ValidationError(
                'The string cannot be shorter than %size% character.',
                'The string cannot be shorter than %size% characters.',
                [
                    '%size%' => $this->constraints['minStringLength'],
                ]
            );
            $isValid = false;
        }

        return $isValid;
    }
}
