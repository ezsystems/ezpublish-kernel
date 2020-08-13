<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentValidator;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Repository\Validator\ContentValidator;

/**
 * @internal Meant for internal use by Repository
 */
final class ContentValidatorStrategy implements ContentValidator
{
    /** @var \eZ\Publish\SPI\Repository\Validator\ContentValidator[] */
    private $contentValidators;

    public function __construct(iterable $contentValidators)
    {
        $this->contentValidators = $contentValidators;
    }

    public function supports(ValueObject $object): bool
    {
        foreach ($this->contentValidators as $contentValidator) {
            if ($contentValidator->supports($object)) {
                return true;
            }
        }

        return false;
    }

    public function validate(
        ValueObject $object,
        array $context = [],
        ?array $fieldIdentifiers = null
    ): array {
        $fieldErrors = [];
        $validatorFound = false;

        foreach ($this->contentValidators as $contentValidator) {
            if ($contentValidator->supports($object)) {
                $validatorFound = true;

                $fieldErrors = $this->mergeErrors(
                    $fieldErrors,
                    $contentValidator->validate($object, $context, $fieldIdentifiers)
                );
            }
        }

        if ($validatorFound) {
            return $fieldErrors;
        }

        throw new InvalidArgumentException('$object', sprintf(
            'Validator for %s type not found.', get_class($object)
        ));
    }

    private function mergeErrors(
        array $fieldErrors,
        array $foundErrors
    ): array {
        foreach ($foundErrors as $fieldId => $errors) {
            $fieldErrors[$fieldId] = empty($fieldErrors[$fieldId])
                ? $errors
                : array_merge(
                    $fieldErrors[$fieldId],
                    $errors
                );
        }

        return $fieldErrors;
    }
}
