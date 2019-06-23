<?php

/**
 * File containing the EmailAddressValidator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator for checking validity of email addresses. Both form and MX record validity checking are provided.
 *
 * @property int $maxStringLength The maximum allowed length of the string.
 * @property int $minStringLength The minimum allowed length of the string.
 */
class EmailAddressValidator extends Validator
{
    protected $constraints = [
        'Extent' => false,
    ];

    protected $constraintsSchema = [
        'Extent' => [
            'type' => 'string',
            'default' => 'regex',
        ],
    ];

    /**
     * @abstract
     *
     * @param mixed $constraints
     *
     * @return mixed
     */
    public function validateConstraints($constraints)
    {
        $validationErrors = [];
        foreach ($constraints as $name => $value) {
            switch ($name) {
                case 'Extent':

                    if ($value !== false && $value !== 'regex') {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be regex for now",
                            null,
                            [
                                '%parameter%' => $name,
                            ],
                            "[EmailAddressValidator][$name]"
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        [
                            '%parameter%' => $name,
                        ],
                        "[EmailAddressValidator][$name]"
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
     * @abstract
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    public function validate(BaseValue $value)
    {
        $pattern = '/^((\"[^\"\f\n\r\t\v\b]+\")|([A-Za-z0-9_\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[A-Za-z0-9_\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]{2,}))$/';

        if (preg_match($pattern, $value->email)) {
            return true;
        }

        $this->errors[] = new ValidationError(
            'The value must be a valid email address.',
            null,
            [],
            'email'
        );

        return false;
    }
}
