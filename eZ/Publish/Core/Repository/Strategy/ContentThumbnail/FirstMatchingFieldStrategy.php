<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentThumbnail;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy as ContentFieldThumbnailStrategy;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;

final class FirstMatchingFieldStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    private $fieldTypeService;

    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy */
    private $contentFieldStrategy;

    public function __construct(
        ContentFieldThumbnailStrategy $contentFieldStrategy,
        FieldTypeService $fieldTypeService
    ) {
        $this->contentFieldStrategy = $contentFieldStrategy;
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getThumbnail(ContentType $contentType, array $fields): ?Thumbnail
    {
        $fieldDefinitions = $contentType->getFieldDefinitions();

        foreach ($fieldDefinitions as $fieldDefinition) {
            $field = $this->getFieldByIdentifier($fieldDefinition->identifier, $fields);

            if ($field === null) {
                continue;
            }

            $fieldType = $this->fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);

            if (
                $fieldDefinition->isThumbnail
                && $this->contentFieldStrategy->hasStrategy($field->fieldTypeIdentifier)
                && !$fieldType->isEmptyValue($field->value)
            ) {
                return $this->contentFieldStrategy->getThumbnail($field);
            }
        }

        return null;
    }

    private function getFieldByIdentifier(string $identifier, array $fields): ?Field
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Field $field */
        foreach ($fields as $field) {
            if ($field->fieldDefIdentifier === $identifier) {
                return $field;
            }
        }

        return null;
    }
}
