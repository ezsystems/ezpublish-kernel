<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class InputHandler implements InputHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory
     */
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
    private $schemaValidator;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface
     */
    private $docbookValidator;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RelationProcessor
     */
    private $relationProcessor;

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory $domDocumentFactory
     * @param \eZ\Publish\Core\FieldType\RichText\ConverterDispatcher $inputConverter
     * @param \eZ\Publish\Core\FieldType\RichText\Normalizer $inputNormalizer
     * @param \eZ\Publish\Core\FieldType\RichText\ValidatorInterface $schemaValidator
     * @param \eZ\Publish\Core\FieldType\RichText\ValidatorInterface $internalValidator
     * @param \eZ\Publish\Core\FieldType\RichText\RelationProcessor $relationProcessor
     */
    public function __construct(
        DOMDocumentFactory $domDocumentFactory,
        ConverterDispatcher $inputConverter,
        Normalizer $inputNormalizer,
        ValidatorInterface $schemaValidator,
        ValidatorInterface $internalValidator,
        RelationProcessor $relationProcessor
    ) {
        $this->domDocumentFactory = $domDocumentFactory;
        $this->converter = $inputConverter;
        $this->normalizer = $inputNormalizer;
        $this->schemaValidator = $schemaValidator;
        $this->docbookValidator = $internalValidator;
        $this->relationProcessor = $relationProcessor;
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
        $errors = $this->schemaValidator->validateDocument($inputValue);
        if (!empty($errors)) {
            throw new InvalidArgumentException(
                '$inputValue',
                'Validation of XML content failed: ' . implode("\n", $errors)
            );
        }

        return $this->converter->dispatch($inputValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations(DOMDocument $document): array
    {
        return $this->relationProcessor->getRelations($document);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(DOMDocument $document): array
    {
        return $this->docbookValidator->validateDocument($document);
    }
}
