<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation\FixtureGenerator;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

/**
 * @internal
 */
final class FieldAggregationFixtureGenerator
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var string|null */
    private $contentTypeIdentifier;

    /** @var string|null */
    private $fieldTypeIdentifier;

    /** @var string|null */
    private $fieldDefinitionIdentifier;

    /** @var iterable|null */
    private $values;

    /** @var callable|null */
    private $fieldDefinitionCreateStructConfigurator;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function setContentTypeIdentifier(string $contentTypeIdentifier): self
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;

        return $this;
    }

    public function setFieldTypeIdentifier(string $fieldTypeIdentifier): self
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;

        return $this;
    }

    public function setFieldDefinitionIdentifier(string $fieldDefinitionIdentifier): self
    {
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;

        return $this;
    }

    public function setFieldDefinitionCreateStructConfigurator(
        ?callable $fieldDefinitionCreateStructConfigurator
    ): self {
        $this->fieldDefinitionCreateStructConfigurator = $fieldDefinitionCreateStructConfigurator;

        return $this;
    }

    public function setValues(iterable $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function execute(): void
    {
        $contentType = $this->createContentTypeForFieldAggregation(
            $this->contentTypeIdentifier,
            $this->fieldDefinitionIdentifier,
            $this->fieldTypeIdentifier,
            $this->fieldDefinitionCreateStructConfigurator
        );

        $this->createFieldAggregationFixtures(
            $contentType,
            $this->fieldDefinitionIdentifier,
            $this->values
        );
    }

    private function createFieldAggregationFixtures(
        ContentType $contentType,
        string $fieldDefinitionIdentifier,
        iterable $values
    ): void {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        foreach ($values as $value) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField($fieldDefinitionIdentifier, $value);

            $contentService->publishVersion(
                $contentService->createContent(
                    $contentCreateStruct,
                    [
                        $locationService->newLocationCreateStruct(2),
                    ]
                )->versionInfo
            );
        }
    }

    private function createContentTypeForFieldAggregation(
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        string $fieldTypeIdentifier,
        ?callable $fieldDefinitionCreateStructConfigurator = null
    ): ContentType {
        $contentTypeService = $this->repository->getContentTypeService();

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct($contentTypeIdentifier);
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        $contentTypeCreateStruct->names = [
            'eng-GB' => 'Field aggregation',
        ];

        $contentTypeCreateStruct->addFieldDefinition(
            $this->createFieldDefinitionCreateStruct(
                $fieldDefinitionIdentifier,
                $fieldTypeIdentifier,
                $fieldDefinitionCreateStructConfigurator
            )
        );

        $contentTypeDraft = $contentTypeService->createContentType(
            $contentTypeCreateStruct,
            [
                $contentTypeService->loadContentTypeGroupByIdentifier('Content'),
            ]
        );

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
    }

    private function createFieldDefinitionCreateStruct(
        string $fieldDefinitionIdentifier,
        string $fieldTypeIdentifier,
        ?callable $fieldDefinitionCreateStructConfigurator = null
    ): FieldDefinitionCreateStruct {
        $contentTypeService = $this->repository->getContentTypeService();

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            $fieldDefinitionIdentifier,
            $fieldTypeIdentifier
        );

        $fieldDefinitionCreateStruct->isSearchable = true;
        $fieldDefinitionCreateStruct->names = [
            'eng-GB' => 'Aggregated field',
        ];

        if ($fieldDefinitionCreateStructConfigurator !== null) {
            // Configure field definition
            $fieldDefinitionCreateStructConfigurator($fieldDefinitionCreateStruct);
        }

        return $fieldDefinitionCreateStruct;
    }
}
