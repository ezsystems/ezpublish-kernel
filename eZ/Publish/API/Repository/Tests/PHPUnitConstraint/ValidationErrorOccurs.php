<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\PHPUnitConstraint;

/**
 * PHPUnit constraint checking that the given ValidationError message occurs in asserted ContentFieldValidationException.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
 * @see \eZ\Publish\SPI\FieldType\ValidationError
 */
class ValidationErrorOccurs extends AllValidationErrorsOccur
{
    /** @var string */
    private $expectedValidationErrorMessage;

    /**
     * @param string $expectedValidationErrorMessage
     */
    public function __construct(string $expectedValidationErrorMessage)
    {
        $this->expectedValidationErrorMessage = $expectedValidationErrorMessage;

        parent::__construct([$expectedValidationErrorMessage]);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return "contain the message '{$this->expectedValidationErrorMessage}'";
    }
}
