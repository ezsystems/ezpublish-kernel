<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;

class ValidatorAggregate implements ValidatorInterface
{
    /** @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface[] */
    private $validators;

    /**
     * @param iterable $validators
     */
    public function __construct(iterable $validators)
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function validateDocument(DOMDocument $xmlDocument): array
    {
        $validationErrors = [];

        foreach ($this->validators as $validator) {
            foreach ($validator->validateDocument($xmlDocument) as $error) {
                $validationErrors[] = $error;
            }
        }

        return $validationErrors;
    }
}
