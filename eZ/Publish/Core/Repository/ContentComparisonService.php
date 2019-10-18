<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentComparisonService as ContentComparisonInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Comparison\ComparisonEngineRegistry;
use eZ\Publish\Core\Comparison\FieldRegistry;
use eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;

class ContentComparisonService implements ContentComparisonInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    private $contentTypeHandler;

    /** @var \eZ\Publish\Core\Comparison\FieldRegistry */
    private $fieldRegistry;

    /** @var \eZ\Publish\Core\Comparison\ComparisonEngineRegistry */
    private $comparatorEngineRegistry;

    /** @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper */
    private $contentTypeDomainMapper;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        ContentHandler $contentHandler,
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        ComparisonEngineRegistry $comparatorEngineRegistry,
        ContentTypeDomainMapper $contentTypeDomainMapper,
        PermissionResolver $permissionResolver
    ) {
        $this->contentHandler = $contentHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->comparatorEngineRegistry = $comparatorEngineRegistry;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->permissionResolver = $permissionResolver;
    }

    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff {
        if ($versionA->getContentInfo()->id !== $versionB->getContentInfo()->id) {
            throw new InvalidArgumentException(
                '$versionB',
                'Version B is not version of the same content as $versionA'
            );
        }
        $languageCode = $languageCode ?? $versionA->initialLanguageCode;

        if (!in_array($languageCode, $versionA->languageCodes) || !in_array($languageCode, $versionB->languageCodes)) {
            throw new InvalidArgumentException(
                '$languageCode',
                sprintf("Language '%s' must be present in both given Versions", $languageCode)
            );
        }

        if (!$this->permissionResolver->canUser('content', 'versionread', $versionA)) {
            throw new UnauthorizedException('content', 'versionread', ['contentId' => $versionA->getContentInfo()->id]);
        }

        $content = $this->contentHandler->load($versionA->getContentInfo()->id, $versionA->versionNo, [$languageCode]);
        $contentToCompare = $this->contentHandler->load($versionB->getContentInfo()->id, $versionB->versionNo, [$languageCode]);
        $fieldsDiff = [];
        foreach ($content->fields as $field) {
            $comparableField = $this->fieldRegistry->getType($field->type);
            $matchingField = $this->getMatchingField($field, $contentToCompare);
            $fieldDefinition = $this->contentTypeHandler->getFieldDefinition($field->fieldDefinitionId, Type::STATUS_DEFINED);
            $dataA = $comparableField->getDataToCompare(
                $field->value
            );
            $dataB = $comparableField->getDataToCompare(
                $matchingField->value
            );
            $engine = $this->comparatorEngineRegistry->getEngine($dataA->getType());

            $diff = $engine->compareFieldsData($dataA, $dataB);
            $fieldsDiff[$fieldDefinition->identifier] = new FieldDiff(
                $this->contentTypeDomainMapper->buildFieldDefinitionDomainObject(
                    $fieldDefinition,
                    $languageCode
                ),
                $diff
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

        throw new InvalidArgumentException(
            '$field',
            sprintf("Field with id : '%d' was not found in content with id: '%d'",
                $field->fieldDefinitionId,
                $contentToCompare->versionInfo->contentInfo->id
            )
        );
    }
}
