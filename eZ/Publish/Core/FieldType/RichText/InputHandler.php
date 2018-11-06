<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class InputHandler
{
    /** @var \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory */
    private $domDocumentFactory;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher
     */
    private $converter;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\Normalizer
     */
    private $normalizer;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface
     */
    private $validator;

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory $domDocumentFactory
     * @param \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher $inputConverter
     * @param \eZ\Publish\Core\FieldType\RichText\Normalizer $inputNormalizer
     * @param \eZ\Publish\Core\FieldType\RichText\ValidatorInterface $inputValidator
     */
    public function __construct(
        DOMDocumentFactory $domDocumentFactory,
        ConverterDispatcher $inputConverter,
        Normalizer $inputNormalizer,
        ValidatorInterface $inputValidator
    ) {
        $this->domDocumentFactory = $domDocumentFactory;
        $this->converter = $inputConverter;
        $this->normalizer = $inputNormalizer;
        $this->validator = $inputValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(?string $inputValue = null): DOMDocument
    {
        if (empty($inputValue)) {
            $inputValue = Value::EMPTY_VALUE;
        }

        if ($this->normalizer->accept($inputValue)) {
            $inputValue = $this->normalizer->normalize($inputValue);
        }

        return $this->fromDocument($this->domDocumentFactory->loadXMLString($inputValue));
    }

    /**
     * {@inheritdoc}
     */
    public function fromDocument(DOMDocument $inputValue): DOMDocument
    {
        $errors = $this->validator->validateDocument($inputValue);
        if (!empty($errors)) {
            throw new InvalidArgumentException(
                '$inputValue',
                'Validation of XML content failed: ' . implode("\n", $errors)
            );
        }

        return $this->converter->dispatch($inputValue);
    }
}
