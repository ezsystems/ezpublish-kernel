<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ComparingService as ComparingServiceInterface;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Compare\CompareEngineRegistry;
use eZ\Publish\Core\Compare\FieldRegistry;
use eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;

class ComparingService implements ComparingServiceInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    private $contentTypeHandler;

    /** @var \eZ\Publish\Core\Compare\FieldRegistry */
    private $fieldRegistry;

    /** @var \eZ\Publish\Core\Compare\CompareEngineRegistry */
    private $comparatorEngineRegistry;

    /** @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper */
    private $contentTypeDomainMapper;

    public function __construct(
        ContentHandler $contentHandler,
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        CompareEngineRegistry $comparatorEngineRegistry,
        ContentTypeDomainMapper $contentTypeDomainMapper
    ) {
        $this->contentHandler = $contentHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->comparatorEngineRegistry = $comparatorEngineRegistry;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
    }

    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff {
        $content = $this->contentHandler->load($versionA->getContentInfo()->id, $versionA->versionNo);
        $contentToCompare = $this->contentHandler->load($versionB->getContentInfo()->id, $versionB->versionNo);
        $fieldsDiff = [];
        foreach ($content->fields as $field) {
            $comparableField = $this->fieldRegistry->getType($field->type);
            $matchingField = $this->getMatchingField($field, $contentToCompare);
            $fieldDefinition = $this->contentTypeHandler->getFieldDefinition($field->id, Type::STATUS_DEFINED);
            $dataA = $comparableField->getDataToCompare(
                $field->value
            );
            $dataB = $comparableField->getDataToCompare(
                $matchingField->value
            );
            $diffs = [];
            foreach ($dataA as $name => $fieldAData) {
                $engine = $this->comparatorEngineRegistry->getEngine(get_class($fieldAData));

                $diffs[$name] = $engine->compareFieldsData($fieldAData, $dataB[$name]);
            }
            $fieldsDiff[$field->fieldDefinitionId] = new FieldDiff(
                $this->contentTypeDomainMapper->buildFieldDefinitionDomainObject(
                    $fieldDefinition,
                    $languageCode ?? $versionA->initialLanguageCode
                ),
                $diffs
            );
        }

        return new VersionDiff($fieldsDiff);
    }

    private function getMatchingField(
        Field $field,
        Content $contentToCompare
    ): Field {
        foreach ($contentToCompare->fields as $fieldToCompare) {
            if ($fieldToCompare->fieldDefinitionId === $field->fieldDefinitionId) {
                return $fieldToCompare;
            }
        }
    }
}
