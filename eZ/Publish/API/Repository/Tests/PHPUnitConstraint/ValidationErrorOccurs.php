<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\PHPUnitConstraint;

use PHPUnit\Framework\Constraint\Constraint as AbstractPHPUnitConstraint;

/**
 * PHPUnit constraint checking that the given ValidationError message occurs in asserted ContentFieldValidationException.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
 * @see \eZ\Publish\SPI\FieldType\ValidationError
 */
class ValidationErrorOccurs extends AbstractPHPUnitConstraint
{
    /** @var string */
    private $expectedValidationErrorMessage;

    /**
     * @param string $expectedValidationErrorMessage
     */
    public function __construct($expectedValidationErrorMessage)
    {
        parent::__construct();

        $this->expectedValidationErrorMessage = $expectedValidationErrorMessage;
    }

    /**
     * @param \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $other
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        foreach ($other->getFieldErrors() as $fieldId => $errors) {
            foreach ($errors as $languageCode => $fieldErrors) {
                foreach ($fieldErrors as $fieldError) {
                    /** @var \eZ\Publish\Core\FieldType\ValidationError $fieldError */
                    if ($fieldError->getTranslatableMessage()->message === $this->expectedValidationErrorMessage) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $other
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        return sprintf(
            '%s::getFieldErrors = %s %s',
            get_class($other),
            var_export($other->getFieldErrors(), true),
            $this->toString()
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return "contains a ValidationError with the message '{$this->expectedValidationErrorMessage}'";
    }
}
